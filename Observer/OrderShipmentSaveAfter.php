<?php
namespace Vtex\VtexMagento\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Vtex\VtexMagento\Api\ApiVtex;
use Vtex\VtexMagento\Api\Vtex;
use Vtex\VtexMagento\Helpers\VtexOrdersHelper;

class OrderShipmentSaveAfter implements ObserverInterface
{
    protected $vClient;
    protected $apiVClient;
    protected $orderHelper;

    public function __construct(
        Vtex $vClient,
        ApiVtex $apiVClient,
        VtexOrdersHelper $vtexOrderHelper
    ) {
        $this->vClient = $vClient;
        $this->apiVClient = $apiVClient;
        $this->vtexOrderHelper = $vtexOrderHelper;
    }

    public function execute(Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        if ($vtexOrderId = $this->vtexOrderHelper->getVtexOrderId($shipment->getOrderId())) {
            if ($shipment instanceof \Magento\Framework\Model\AbstractModel) {

                $order = $shipment->getOrder();
                $invoiceNumber = $order->getInvoiceCollection()->getData()[0]['entity_id'];

                foreach ($shipment->getTracks() as $track) {
                    $this->vClient->sendTracking($vtexOrderId, $invoiceNumber, $track);
                }

            }
        }
        return $this;
    }
}
