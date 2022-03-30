<?php

namespace Vtex\VtexMagento\Helpers;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\Interceptor as Product;
use Psr\Log\LoggerInterface;
use Vtex\VtexMagento\Model\ResourceModel\Mapping\CollectionFactory as MappingCollectionFactory;
use Vtex\VtexMagento\Model\ResourceModel\Settings\CollectionFactory as SettingsCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as GroupCollectionFactory;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\ResponseFactory;

class ProductHelper extends AbstractHelper
{
    protected $_storeManager;
    protected $productRepository;
    protected $vtexPriceMultiplier = 100;
    protected $_settingsCollectionFactory = null;
    protected $sellerId;
    protected $quote;
    protected $countriesHelper;
    protected $groupCollectionFactory;
    protected $logger;
    protected $settings;
    protected $client;
    protected $_mappingCollectionFactory = null;
    protected $responseFactory;
    protected $clientFactory;

    protected $api_endpoint = "https://app.io.vtex.com/";

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        SettingsCollectionFactory $settingsCollectionFactory,
        QuoteFactory $quote,
        CountriesHelper $countriesHelper,
        GroupCollectionFactory $groupCollectionFactory,
        LoggerInterface $logger,
        MappingCollectionFactory $mappingCollectionFactory,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory
    )
    {
        $this->_storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->_settingsCollectionFactory = $settingsCollectionFactory;
        $this->quote = $quote;
        $this->countriesHelper = $countriesHelper;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->logger = $logger;
        $this->_mappingCollectionFactory = $mappingCollectionFactory;

        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;

        $settingsCollection = $this->_settingsCollectionFactory->create();
        $settingsCollection->addFieldToSelect('*')->load();
        if($settingsCollection->getItems()) {
            $settings = $settingsCollection->getFirstItem();
            $this->settings = $settings;
            $this->sellerId = $settings->getSellerId();
            $this->client = new Client(['base_uri' => str_replace('{{vendor}}', $settings->getVendorName(), $this->api_endpoint),
                'headers' => ['Content-Type' => 'application/json; charset=utf-8',
                    'Accept' => 'application/json',
                    'X-VTEX-API-AppKey' => $settings->getAppKey(),
                    'X-VTEX-API-AppToken' => $settings->getAppToken(),
                    'VtexIdclientAutCookie' => $settings->getVtexAutCookie()
                ],
                'verify' => false]);
        }

        parent::__construct($context);
    }

    public function getProductData(Product $product, $sellerId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $data = $product->getData();
        $currency = $this->_storeManager->getStore()->getCurrentCurrency();
        $categoryName = 'NONAME';
        if (isset($product->getCategoryIds()[0])) {
            $category = $objectManager->create('Magento\Catalog\Model\Category')->load($product->getCategoryIds()[0]);
            $categoryName = $category->getName();
        }
        $brand = $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product);
        $brand = $brand?:'NONAME';

        $sku = "{$sellerId}-{$data['entity_id']}";
        $name = strlen($data['name']) > 127 ? substr($data['name'], 0, 127) : $data['name'];

        return [
            'ProductName' => $name,
            'ProductId' => $data['entity_id'],
            'ProductDescription' => isset($data['description']) ? $data['description'] : '',
            'BrandName' => $brand,
            'SkuName' => $name,
            'SellerId' => $sellerId,
            'Height' => 1,
            'Width' => 1,
            'Length' => 1,
            'WeightKg' => isset($data['weight']) ? $data['weight'] : 1,
            'RefId' => $sku,
            'SellerStockKeepingUnitId' => $data['entity_id'],
            'CategoryFullPath' => $categoryName,
            'SkuSpecifications' => $this->getSpecifications($product),
            'ProductSpecifications' => $this->getSpecifications($product),
            'Images' => $this->getImages($product),
            'MeasurementUnit' => 'un',
            'UnitMultiplier' => 1,
            'AvailableQuantity' => isset($data['stock_data']) ? $data['stock_data']['qty'] : 0,
            'Pricing' => [
                'Currency' => $currency->getCurrencyCode(),
                'SalePrice' => $data['price'],
                'CurrencySymbol' => $currency->getCurrencySymbol(),
            ],
        ];
    }

    public function getImages(Product $product)
    {
        $response = [];
        $product->load('media_gallery');
        if ($images = $product->getData('media_gallery')) {
            $this->logger->debug($product->getName() . ", baseUrl({$this->_storeManager->getStore()->getBaseUrl()}), has images: " . json_encode($images['images']));
            foreach ($images['images'] as $image) {
                $response[] = [
                    'imageName' => "Image{$image['position']}",
                    'imageUrl' => PHP_SAPI == 'cli'
                        ? str_replace(['/magento/', 'http://'], ['/', 'https://'], $this->_storeManager->getStore()->getBaseUrl()) . 'media/catalog/product' . $image['file']
                        : str_replace('index.php/', '', $this->_storeManager->getStore()->getBaseUrl()) . 'media/catalog/product' . $image['file']
                ];
            }
        } else {
            $this->logger->debug($product->getName() . ' has no images');
        }
        return $response;
    }

    public function getProductById($id)
    {
        return $this->productRepository->getById($id);
    }

    public function getRequestProducts($request)
    {
        $response = [];
        foreach ($request['items'] as $k => $item) {
            $prod = $this->getProductById($item['id']);
            $data = $prod->getData();

            $response[] = [
                'id' => $data['entity_id'],
                'requestIndex' => $k,
                'quantity' => $item['quantity'],
                'price' => $data['price'] * $this->vtexPriceMultiplier,
                'listPrice' => $data['price'] * $this->vtexPriceMultiplier,
                'sellingPrice' => $data['price'] * $this->vtexPriceMultiplier,
                'measurementUnit' => 'un',
                'merchantName' => null,
                'priceValidUntil' => null,
                'seller' => $this->sellerId,
                'unitMultiplier' => 1,
                'attachmentOfferings' => [],
                'offerings' => [],
                'priceTags' => [],
                'availability' => ($item['quantity'] > 0) ? 'available' : 'unavailable',
            ];
        }
        return $response;
    }

    public function getLogisticsInfo($request)
    {
        $quote = $this->quote->create();
        $quote->setStore($this->_storeManager->getStore());
        foreach ($request['items'] as $k => $item) {
            $quote->addProduct($this->getProductById($item['id']));
        }
        $quote->getShippingAddress()
            ->setCountryId($this->countriesHelper->getCountryCode($request['country']))
            ->setPostcode($request['postalCode']);
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->collectShippingRates();

        $deliveryPrice = 0;
        $_rates = $quote->getShippingAddress()->getShippingRatesCollection()->toArray();
        foreach ($_rates['items'] as $item) {
            if ($item['code'] == 'flatrate_flatrate') {
                $deliveryPrice = $item['price'] * $this->vtexPriceMultiplier;
                break;
            }
        }

        $response = [];
        foreach ($request['items'] as $k => $item) {
            $prod = $this->getProductById($item['id']);
            $data = $prod->getData();

            $response[] = [
                'itemIndex' => $k,
                'quantity' => $item['quantity'],
                'stockBalance' => $data['quantity_and_stock_status']['qty'],
                'shipsTo' => [
                    isset($request['country']) ? $request['country'] : null
                ],
                'slas' => [
                    [
                        'id' => 'Normal',
                        'deliveryChannel' => 'delivery',
                        'name' => 'Normal',
                        'shippingEstimate' => '1bd',
                        'price' => $deliveryPrice / count($request['items']),
                    ]
                ],
                'deliveryChannels' => [
                    [
                        'id' => 'delivery',
                        'stockBalance' => $data['quantity_and_stock_status']['qty']
                    ]
                ]
            ];
        }
        return $response;
    }

    public function getInvoiceData($invoice)
    {
        $items = [];
        foreach ($invoice->getAllItems() as $item) {
            $items[] = [
                'id' => $item->getProductId(),
                'price' => $item->getPrice() * $this->vtexPriceMultiplier,
                'quantity' => (int)$item->getQty()
            ];
        }

        return [
            'type' => 'Output',
            'invoiceNumber' => $invoice->getEntityId(),
            'invoiceValue' => $invoice->getGrandTotal() * $this->vtexPriceMultiplier,
            'issuanceDate' => date(DATE_ISO8601, strtotime($invoice->getCreatedAt())),
            'invoiceUrl' => str_replace('index.php/', '', $this->_storeManager->getStore()->getBaseUrl()) . 'rest/fulfillment/mgt/invoices/' . $invoice->getEntityId(),
            'items' => $items,
            'courier' => '',
            'trackingNumber' => '',
            'trackingUrl' => ''
        ];
    }

    public function getSpecifications(Product $product, $detailed = false)
    {
        $response = [];

        $attributeSetId = $product->getAttributeSetId();
        $groupCollection = $this->groupCollectionFactory->create()
            ->setAttributeSetFilter($attributeSetId)
            ->setSortOrder()
            ->load();

        foreach ($groupCollection as $group) {
            $attributes = $product->getAttributes($group->getId(), true);
            foreach ($attributes as $key => $attribute) {
                if($attribute->getIsVisibleOnFront()
                    && $attribute->getFrontend()->getValue($product) !=""
                    && $attribute->getFrontend()->getValue($product) !="Non"
                    && $attribute->getFrontend()->getLabel() != "Manufacturer"
                ) {
                    $type = 'Combo';
                    $value = $attribute->getFrontend()->getValue($product);
                    if ($value instanceof \Magento\FrameWork\Phrase) {
                        $value = $value->getText();
                        $type = 'Radio';
                    }

                    $fields = [
                        'FieldName' => $attribute->getFrontend()->getLabel(),
                        'FieldValues' => [$value],
                    ];

                    if ($detailed) {
                        $fields['Type'] = $type;
                    }

                    $response[] = $fields;
                }
            }
        }

        return $response;
    }

    public function getProductDataV2($product, $brandId)
    {
        $data = $product->getData();

        $categoryIds = [];
        foreach ($product->getCategoryIds() as $categoryId) {
            $map = $this->_mappingCollectionFactory->create()->addFilter('magento_id', $categoryId)->getFirstItem();
            if ($map->getVtexId()) {
                $categoryIds[] = $map->getVtexId();
            }
        }

        $sku = "{$this->sellerId}-{$data['entity_id']}";

        return [
            'id' => NULL,
            'externalId' => $sku,
            'status' => 'active',
            'condition' => 'new',
            'type' => 'physical',
            'name' => $data['name'],
            'description' => isset($data['description']) ? $data['description'] : '',
            'brandId' => $brandId,
            'categoryIds' => $categoryIds,
            'salesChannels' => [
                '1',
            ],
            'specs' => $this->getSpecificationsV2($product),
            'attributes' => $this->getAttributesV2($product),
//            'slug' => '',
            'images' => $this->getImagesV2($product),
            'skus' => [
                [
                    'id' => NULL,
                    'externalId' => $sku,
                    'code' => $sku,
                    'sellers' => [
                        [
                            'id' => '1',
                        ],
                    ],
                    'isActive' => true,
                    'weight' => isset($data['weight']) ? $data['weight'] : 1,
                    'dimensions' => [
                        'width' => 1,
                        'height' => 1,
                        'length' => 1,
                    ],
                    'specs' => $this->getAttributesV2($product)
                ]
            ],
        ];
    }

    public function getSpecificationsV2($product)
    {
        $response = [];

        $attributeSetId = $product->getAttributeSetId();
        $groupCollection = $this->groupCollectionFactory->create()
            ->setAttributeSetFilter($attributeSetId)
            ->setSortOrder()
            ->load();

        foreach ($groupCollection as $group) {
            $attributes = $product->getAttributes($group->getId(), true);
            foreach ($attributes as $key => $attribute) {
                if($attribute->getIsVisibleOnFront()
                    && $attribute->getFrontend()->getValue($product) !=""
                    && $attribute->getFrontend()->getValue($product) !="Non"
                    && $attribute->getFrontend()->getLabel() != "Manufacturer"
                ) {

                    $value = $attribute->getFrontend()->getValue($product);
                    if ($value instanceof \Magento\FrameWork\Phrase) {
                        $value = $value->getText();
                    }

                    $fields = [
                        'name' => $attribute->getFrontend()->getLabel(),
                        'values' => [$value],
                    ];

                    $response[] = $fields;
                }
            }
        }

        return $response;
    }

    public function getAttributesV2($product)
    {
        $response = [];

        $attributeSetId = $product->getAttributeSetId();
        $groupCollection = $this->groupCollectionFactory->create()
            ->setAttributeSetFilter($attributeSetId)
            ->setSortOrder()
            ->load();

        foreach ($groupCollection as $group) {
            $attributes = $product->getAttributes($group->getId(), true);
            foreach ($attributes as $key => $attribute) {
                if($attribute->getIsVisibleOnFront()
                    && $attribute->getFrontend()->getValue($product) !=""
                    && $attribute->getFrontend()->getValue($product) !="Non"
                    && $attribute->getFrontend()->getLabel() != "Manufacturer"
                ) {

                    $value = $attribute->getFrontend()->getValue($product);
                    if ($value instanceof \Magento\FrameWork\Phrase) {
                        $value = $value->getText();
                    }

                    $fields = [
                        'name' => $attribute->getFrontend()->getLabel(),
                        'value' => $value,
                        'description' => '',
                        'isFilterable' => true,
                    ];

                    $response[] = $fields;
                }
            }
        }

        return $response;
    }

    private function uploadToVtex($image) {
        $this->logger->debug($image['file'] . ' to upload');
        $image_path = 'media/catalog/product' . $image['file'];
        $path_parts = pathinfo($image_path);
        $file_name = uniqid('magento_').'.'.$path_parts['extension'];
        $url2 = 'https://app.io.vtex.com/vtex.catalog-images/v0/'.$this->settings->getVendorName().'/master/images/save/'.$file_name;
        try {
            $response = $this->client->request('POST', $url2, [
                'multipart' => [
                    [
                        'name'     => $file_name,
                        'contents' => fopen($image_path, 'r'),
                    ]
                ],
            ]);

            $body = json_decode($response->getBody()->getContents());
            if (isset($body->fullUrl)) {
                return $body->fullUrl;
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return null;


    }

    public function getImagesV2($product)
    {
        $response = [];
        $product->load('media_gallery');
        if ($images = $product->getData('media_gallery')) {
            foreach ($images['images'] as $image) {
                $url = $this->uploadToVtex($image);
                if ($url != null) {
                    $this->logger->debug($url);
                    $response[] = [
                        'id' => "Image{$image['position']}",
                        'alt' => $image['file'],
                        'url' => $url
                    ];
                }
            }
        } else {
            $this->logger->debug($product->getName() . ' has no images');
        }
        return $response;
    }
}
