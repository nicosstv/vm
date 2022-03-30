<?php

namespace Vtex\VtexMagento\Helpers;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Vtex\VtexMagento\Service\ImportImageService;
use Vtex\VtexMagento\Model\VtexOrdersFactory;
use Magento\Directory\Model\ResourceModel\Region\Collection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Vtex\VtexMagento\Model\PaymentMethodsMapping;
use Vtex\VtexMagento\Model\Coupon;
use Psr\Log\LoggerInterface;

class OrderHelper extends AbstractHelper
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var PaymentInterface
     */
    protected $orderPaymentInterface;

    /**
     * @var QuoteFactory
     */
    protected $quote;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var Store
     */
    protected $storeModel;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var ImportImageService
     */
    protected $importImageService;

    /**
     * @var VtexOrdersFactory
     */
    protected $vtexOrders;

    /**
     * @var VtexOrdersHelper
     */
    protected $vtexOrdersHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var PaymentMethodsMapping
     */
    protected $_paymentMethodsMapping;

    protected $logger;

    protected $couponHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param QuoteFactory $quote
     * @param QuoteManagement $quoteManagement
     * @param OrderSender $orderSender
     * @param Store $storeModel
     * @param PaymentInterface $orderPaymentInterface
     * @param Product $product
     * @param ImportImageService $importImageService
     * @param VtexOrdersFactory $vtexOrders
     * @param VtexOrdersHelper $vtexOrdersHelper
     * @param PaymentMethodsMapping $paymentMethodsMapping
     */

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        PageFactory $resultPageFactory,
        OrderRepositoryInterface $orderRepository,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        QuoteFactory $quote,
        QuoteManagement $quoteManagement,
        OrderSender $orderSender,
        Store $storeModel,
        PaymentInterface $orderPaymentInterface,
        Product $product,
        ImportImageService $importImageService,
        VtexOrdersFactory $vtexOrders,
        VtexOrdersHelper $vtexOrdersHelper,
        PaymentMethodsMapping $paymentMethodsMapping,
        Coupon $couponHelper,
        CollectionFactory $collectionFactory
    )
    {
        $this->logger = $logger;
        $this->cuponHelper = $couponHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->orderSender = $orderSender;
        $this->storeModel = $storeModel;
        $this->orderPaymentInterface = $orderPaymentInterface;
        $this->product = $product;
        $this->importImageService = $importImageService;
        $this->vtexOrders = $vtexOrders;
        $this->vtexOrdersHelper = $vtexOrdersHelper;
        $this->_paymentMethodsMapping = $paymentMethodsMapping;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     *
     * @param array $orderInfo
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function create($orderInfo = [])
    {
        $stores = $this->storeManager->getStores();
        $store = null;
        foreach ($stores as $storeObj) {
            if ($storeObj->getCode() === 'vtex') {
                $store = $storeObj;
                break;
            }
        }
        $store = $store?:$stores[1];
        $websiteId = $store->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderInfo['customer']['email']);
        if (!$customer->getEntityId()) {

            $customer->setWebsiteId($websiteId);
            $customer->setStore($store);
            $customer_info = [
                'firstname' => $orderInfo['customer']['firstName'],
                'lastname' => $orderInfo['customer']['lastName'],
                'email' => $orderInfo['customer']['email'],
                'password' => $orderInfo['customer']['email']
            ];
            $customer->addData($customer_info);
            $customer->save();
        }


        $quote = $this->quote->create();
        $quote->setStore($store);
        $customer = $this->customerRepository->getById($customer->getEntityId());
        $quote->setCurrency();
        $quote->assignCustomer($customer);

        // Check VTEX to Magento payment methods mapping
        $payment_method_code = $this->_paymentMethodsMapping->getByName($orderInfo['invoiceData']['userPaymentInfo']['paymentMethods'][0]);
        $payment_method_code = $payment_method_code != null ? $payment_method_code : 'checkmo';
        //$this->logger->debug('Payment code: '. $payment_method_code);

        //Add Items in Quote Object
        foreach ($orderInfo['items'] as $item) {
            $product = $this->productRepository->getById($item['product_id']);
            $product->setPrice($item['price']);
            $quote->addProduct($product, intval($item['quantity']));
        }

        //region detection
        $region = $this->collectionFactory->create()
            ->addRegionNameFilter($orderInfo['address']['region'])
            ->getFirstItem()
            ->toArray();
        if(isset($region['region_id'])) {
            $orderInfo['address']['region_id'] = $region['region_id'];
        }

        // Payment
        $quote->setPayment($this->orderPaymentInterface->setMethod($payment_method_code));

        $quote->getBillingAddress()->addData($orderInfo['address']);
        $quote->getShippingAddress()->addData($orderInfo['address']);

        // Set Shipping Method
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate');
        //$quote->save();

        // Discount
        $discount_total = 0;
        $discount_name = '';
        foreach ($orderInfo['items'] as $item) {
            if(isset($item['priceTags']) && count($item['priceTags']) > 0) {
                $discount_total += -($item['priceTags'][0]->value / 100);
                $discount_name = $item['priceTags'][0]->name;
            }
        }

        if ($coupon = $this->cuponHelper->createRule(array('name' => uniqid($discount_name.'_'), 'value'=>round($discount_total, 2)))) {
            $quote->setCouponCode($coupon->getData()['code'])
            ->collectTotals()
            ->save();
        } else {
            $quote->collectTotals()->save();
        }

        $quote->getPayment()->importData(['method' => $payment_method_code]);
        $order = $this->quoteManagement->submit($quote);

        if ($orderId = $order->getEntityId()) {
            // Add to vtex_orders table
            $vtexOrders = $this->vtexOrders->create();
            $vtexOrders->setOrderId($orderId);
            $vtexOrders->setVtexOrderId($orderInfo['entity_id']);
            $vtexOrders->save();

            $result['orderId'] = $orderId;
        } else {
            $result = ['error' => true, 'msg' => 'Error occurs for Order placed'];
        }
        return $result;
    }

    /**
     * @param int $marketplaceOrderId
     * @return Json
     */
    public function cancelOrder($marketplaceOrderId)
    {
        $orderId = 0;

        if($this->vtexOrdersHelper->getOrderId($marketplaceOrderId)) {
            $orderId = $this->vtexOrdersHelper->getOrderId($marketplaceOrderId);
            $objectManager = ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
            $order->setState(Order::STATE_CANCELED)->setStatus(Order::STATE_CANCELED);
            $order->addStatusToHistory($order->getStatus(), 'This order has been cancelled from the marketplace');
            $order->save();
        }

        return $orderId;
    }

    public function fulfilOrder($marketplaceOrderId)
    {
        $orderId = 0;

        if($this->vtexOrdersHelper->getOrderId($marketplaceOrderId)) {
            $orderId = $this->vtexOrdersHelper->getOrderId($marketplaceOrderId);
            $objectManager = ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
            $order->setState(Order::STATE_PROCESSING)->setStatus(Order::STATE_PROCESSING);
            $order->addStatusToHistory($order->getStatus(), 'Marketplace has confirmed payment');
            $order->save();
        }

        return $orderId;
    }

}
