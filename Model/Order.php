<?php

namespace Vtex\VtexMagento\Model;

use Exception;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;
use \Vtex\VtexMagento\Api\OrderInterface;
use Vtex\VtexMagento\Helpers\VtexHelper;
use Vtex\VtexMagento\Helpers\OrderHelper;
use Vtex\VtexMagento\Helpers\CountriesHelper;

class Order implements OrderInterface
{

    protected $order;
    protected $logger;
    protected $request;
    protected $restRequest;
    protected $vtexHelper;
    protected $orderHelper;
    protected $countriesHelper;
    protected $resourceConnection;
    protected $metadataPool;

    public function __construct(
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        Request $restRequest,
        VtexHelper $vtexHelper,
        OrderHelper $orderHelper,
        CountriesHelper $countriesHelper,
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool
    )
    {
        $this->order = $orderRepository;
        $this->request = $request;
        $this->logger = $logger;
        $this->restRequest = $restRequest;
        $this->vtexHelper = $vtexHelper;
        $this->orderHelper = $orderHelper;
        $this->countriesHelper = $countriesHelper;
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }


    /**
     * @param int $id
     * @return Json
     */
    public function getOrder($id)
    {
        $output = [];
        try {
            $order = $this->order->get($id);
            $output['order_id'] = $order->getEntityId();
            $output['state'] = $order->getState();
            $output['status'] = $order->getStatus();
            $output['coupon_code'] = $order->getCouponCode();
            $output['shipping_description'] = $order->getShippingDescription();
            $output['base_discount_amount'] = $order->getBaseDiscountAmount();
            $output['base_grand_total'] = $order->getBaseGrandTotal();
            $output['base_shipping_amount'] = $order->getBaseShippingAmount();
            $output['base_shipping_tax_amount'] = $order->getBaseShippingTaxAmount();
            $output['base_subtotal'] = $order->getBaseSubtotal();
            $output['grand_total'] = $order->getGrandTotal();
            $output['shipping_amount'] = $order->getShippingAmount();
            $output['subtotal'] = $order->getSubtotal();
            $output['total_qty_ordered'] = $order->getTotalQtyOrdered();
            $output['order_currency_code'] = $order->getOrderCurrencyCode();
            $output['customer_email'] = $order->getCustomerEmail();
            $output['customer_firstname'] = $order->getCustomerFirstname();
            $output['customer_lastname'] = $order->getCustomerLastname();
            $output['created_at'] = $order->getCreatedAt();
            $output['updated_at'] = $order->getUpdatedAt();

            foreach ($order->getItems() as $product):
                $output['items'][] = [
                    'item_id' => $product->getItemId(),
                    'product_id' => $product->getProductId(),
                    'order_id' => $product->getOrderId(),
                    'created_at' => $product->getCreatedAt(),
                    'updated_at' => $product->getUpdatedAt(),
                    'product_type' => $product->getProductType(),
                    'product_option' => $product->getProductOption(),
                    'sku' => $product->getSku(),
                    'name' => $product->getName(),
                    'description' => $product->getDescription(),
                    'qty_backordered' => $product->getQtyBackordered(),
                    'qty_canceled' => $product->getQtyCanceled(),
                    'qty_invoiced' => $product->getQtyInvoiced(),
                    'qty_ordered' => $product->getQtyOrdered(),
                    'qty_refunded' => $product->getQtyRefunded(),
                    'qty_shipped' => $product->getQtyShipped(),
                    'base_code' => $product->getBaseCost(),
                    'price' => $product->getPrice(),
                    'base_price' => $product->getBasePrice(),
                    'original_price' => $product->getOriginalPrice(),
                    'base_original_price' => $product->getBaseOriginalPrice(),
                    'tax_percent' => $product->getTaxPercent(),
                    'tax_amount' => $product->getTaxAmount(),
                    'base_tax_amount' => $product->getBaseTaxAmount(),
                    'tax_invoiced' => $product->getTaxInvoiced(),
                    'base_tax_invoiced' => $product->getBaseTaxInvoiced(),
                    'discount_percent' => $product->getDiscountPercent(),
                    'discount_amount' => $product->getDiscountAmount(),
                    'base_discount_amount' => $product->getBaseDiscountAmount(),
                    'discount_invoiced' => $product->getBaseDiscountInvoiced(),
                    'amount_refunded' => $product->getAmountRefunded(),
                    'base_amount_refunded' => $product->getBaseAmountRefunded(),
                    'row_total' => $product->getRowTotal(),
                    'base_row_total' => $product->getBaseRowTotal(),
                    'row_invoiced' => $product->getRowInvoiced(),
                    'base_row_invoiced' => $product->getBaseRowInvoiced(),
                    'price_incl_tax' => $product->getPriceInclTax(),
                    'base_price_incl_tax' => $product->getBasePriceInclTax(),
                    'row_total_incl_tax' => $product->getRowTotalInclTax(),
                    'base_row_total_incl_tax' => $product->getBaseRowTotalInclTax(),
                    'free_shipping' => $product->getFreeShipping(),
                ];
            endforeach;
            $response = ['success' => true, 'message' => '', 'order' => $output];
        } catch (Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage(), 'order' => $output];
            $this->logger->info($e->getMessage());
        }

        return json_encode($response);
    }

    /**
     * @return Json
     */
    public function createOrder()
    {
        $output = [
            'success' => false,
            'message' => 'Invalid data'
        ];
        $request = json_decode($this->restRequest->getContent());
        $this->logger->debug('Create order request: ' . $this->restRequest->getContent());

        if (!isset($request[0])) {
            $output['message'] = 'Wrong data!';
        } else {
            $vtexOrder = $request[0];

            $orderData = [];
            $orderData['customer'] = [
                'firstName' => $vtexOrder->clientProfileData->firstName,
                'lastName' => $vtexOrder->clientProfileData->lastName,
                'email' => $vtexOrder->clientProfileData->email
            ];
            $orderData['entity_id'] = $vtexOrder->marketplaceOrderId;
            $orderData['items'] = [];
            $orderData['address'] = [];
            $orderData['payment'] = [];
            $orderData['statusHistories'] = [];
            $orderData['invoiceData'] = json_decode(json_encode($vtexOrder->invoiceData), true);

            foreach ($vtexOrder->items as $product):
                $orderData['items'][] = [
                    "price" => $this->vtexHelper->formatValue($product->price),
                    "product_id" => $product->id,
                    "product_type" => "simple",
                    "quantity" => $product->quantity,
                    "priceTags" => $product->priceTags
                ];
            endforeach;

            $orderData['address'] = [
                'firstname' => $vtexOrder->clientProfileData->firstName,
                'lastname' => $vtexOrder->clientProfileData->lastName,
                'street' => $vtexOrder->shippingData->address->street . ', ' . $vtexOrder->shippingData->address->number . ', ' . $vtexOrder->shippingData->address->complement,
                'city' => $vtexOrder->shippingData->address->city,
                'country_id' => $this->countriesHelper->getCountryCode($vtexOrder->shippingData->address->country),
                'region' => $vtexOrder->shippingData->address->state,
                'region_id' => '',
                'postcode' => $vtexOrder->shippingData->address->postalCode,
                'telephone' => $vtexOrder->clientProfileData->phone,
                'fax' => '',
                'save_in_address_book' => 1
            ];

            $orderData['payment'] = [
                "amount_ordered" => $this->vtexHelper->formatValue($vtexOrder->marketplacePaymentValue),
                "amount_paid" => 0,
                "base_amount_ordered" => $this->vtexHelper->formatValue($vtexOrder->marketplacePaymentValue),
                "base_amount_paid" => 0,
                "base_shipping_amount" => $this->vtexHelper->formatValue($vtexOrder->shippingData->logisticsInfo[0]->price),
                "shipping_amount" => $this->vtexHelper->formatValue($vtexOrder->shippingData->logisticsInfo[0]->price),
            ];

            $metadata = $this->metadataPool->getMetadata(BulkSummaryInterface::class);
            $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
            $connection->beginTransaction();

            try {
                $result = $this->orderHelper->create($orderData);
                if ($result['orderId']) {
                    $connection->commit();

                    $output = [
                        'marketplaceOrderId' => $vtexOrder->marketplaceOrderId,
                        'orderId' => $result['orderId'],
                        'followUpEmail' => $vtexOrder->clientProfileData->email,
                        'items' => json_decode(json_encode($vtexOrder->items), true),
                        'clientProfileData' => json_decode(json_encode($vtexOrder->clientProfileData), true),
                        'shippingData' => json_decode(json_encode($vtexOrder->shippingData), true),
                        'paymentData' => null
                    ];
                } else {
                    $connection->rollBack();
                }
            } catch (Exception $e) {
                $output = ['message' => $e->getMessage()];
                $connection->rollBack();
            }
        }
        $this->logger->debug('Create order response: ' . json_encode($output));
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode([$output]));
    }

    /**
     * @return Json
     */
    public function cancelOrder()
    {
        $output = [];
        $request = json_decode($this->restRequest->getContent());
        $this->logger->debug('Cancel order request: ' . $this->restRequest->getContent());
        if (!isset($request->marketplaceOrderId)) {
            $output['message'] = 'Wrong data!';
        } else {
            $marketplaceOrderId = $request->marketplaceOrderId;
            $result = $this->orderHelper->cancelOrder($request->marketplaceOrderId);

            // Response ( this format is required for vtex )
            $output = [
                'marketplaceOrderId' => $marketplaceOrderId,
                'date' => date('Y-m-d H:i:s'),
                'orderId' => $result,
                'receipt' => null
            ];
        }

        $this->logger->debug('Cancel order response: ' . json_encode($output));
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($output));
    }

    public function fulfilOrder()
    {
        $output = [];
        $request = json_decode($this->restRequest->getContent());
        $this->logger->debug('Fulfil order request: ' . $this->restRequest->getContent());
        if (!isset($request->marketplaceOrderId)) {
            $output['message'] = 'Wrong data!';
        } else {
            $marketplaceOrderId = $request->marketplaceOrderId;
            $result = $this->orderHelper->fulfilOrder($request->marketplaceOrderId);

            // Response ( this format is required for vtex )
            $output = [
                'date' => date('Y-m-d H:i:s'),
                'marketplaceOrderId' => $marketplaceOrderId,
                'orderId' => $result,
                'receipt' => null
            ];
        }
        $this->logger->debug('Fulfil order response: ' . json_encode($output));
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($output));
    }
}
