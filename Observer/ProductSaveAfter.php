<?php
namespace Vtex\VtexMagento\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Vtex\VtexMagento\Api\ApiVtex;
use Vtex\VtexMagento\Api\Vtex;
use Vtex\VtexMagento\Api\VtexCatalogV2;
use Vtex\VtexMagento\Model\ResourceModel\Settings\CollectionFactory as SettingsCollectionFactory;

class ProductSaveAfter implements ObserverInterface
{
    protected $vClient;
    protected $apiVClient;
    protected $catalogV2;
    protected $_settingsCollectionFactory = null;
    protected $settings;

    public function __construct(
        Vtex $vClient,
        ApiVtex $apiVClient,
        VtexCatalogV2 $catalogV2,
        SettingsCollectionFactory $settingsCollectionFactory

    ) {
        $this->vClient = $vClient;
        $this->apiVClient = $apiVClient;
        $this->catalogV2 = $catalogV2;
        $this->_settingsCollectionFactory = $settingsCollectionFactory;

        $settingsCollection = $this->_settingsCollectionFactory->create();
        $settingsCollection->addFieldToSelect('*')->load();
        if($settingsCollection->getItems()) {
            if ($settings = $settingsCollection->getFirstItem()) {
                $this->settings = $settings;
            }
        }
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();

        if ($product->isDataChanged()) {
            if ($this->settings->getCatalogV2()) {
                if (!$existingProduct = $this->catalogV2->getProduct($product)) {
                    if ($existingProduct = $this->catalogV2->createProduct($product)) {
                        $this->apiVClient->setPriceV2($product, $existingProduct);
                        $this->vClient->setInventoryV2($product, $existingProduct);
                    }
                }
//                else {
//                    if ($this->catalogV2->updateProduct($existingProduct, $product)) {
//                        $this->apiVClient->setPriceV2($product, $existingProduct);
//                        $this->vClient->setInventoryV2($product, $existingProduct);
//                    }
//                }
            } else {
                try {
                    $this->vClient->changeNotification($product->getData('entity_id'));
                } catch (\Exception $e) {
                    $this->apiVClient->sendSKUSuggestion($product);
                }
            }
        }

        return $this;
    }
}
