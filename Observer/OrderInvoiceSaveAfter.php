<?php
namespace Vtex\VtexMagento\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Vtex\VtexMagento\Api\ApiVtex;
use Vtex\VtexMagento\Api\Vtex;
use Vtex\VtexMagento\Helpers\VtexOrdersHelper;

class OrderInvoiceSaveAfter implements ObserverInterface
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
        $invoice = $observer->getEvent()->getInvoice();
        if ($vtexOrderId = $this->vtexOrderHelper->getVtexOrderId($invoice->getOrderId())) {
            if ($invoice instanceof \Magento\Framework\Model\AbstractModel) {

//                $this->vClient->changeOrderState('ready-for-handling', $vtexOrderId);
//                $this->vClient->changeOrderState('start-handling', $vtexOrderId);

                $this->vClient->invoice($vtexOrderId, $invoice);

            }
        }
        return $this;
    }
}
