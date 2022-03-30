<?php
namespace Vtex\VtexMagento\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Vtex\VtexMagento\Api\ApiVtex;
use Vtex\VtexMagento\Api\Vtex;
use Vtex\VtexMagento\Helpers\VtexOrdersHelper;

class OrderSaveAfter implements ObserverInterface
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
        $order = $observer->getEvent()->getOrder();
        if ($vtexOrderId = $this->vtexOrderHelper->getVtexOrderId($order->getEntityId())) {
            if ($order instanceof \Magento\Framework\Model\AbstractModel) {
                if($order->getState() == 'canceled') {
                    $this->vClient->changeOrderState('cancel', $vtexOrderId);
                }
            }
        }
        return $this;
    }
}
