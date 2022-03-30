<?php

namespace Vtex\VtexMagento\Model;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;
use \Vtex\VtexMagento\Api\Data\VtexOrdersInterface;

/**
 * Class File
 * @package Toptal\Blog\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VtexOrders extends AbstractModel implements VtexOrdersInterface, IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'vtex_orders';

    /**
     * Post Initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vtex\VtexMagento\Model\ResourceModel\VtexOrders');
    }

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }


    /**
     * Get Title
     *
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Get Content
     *
     * @return string|null
     */
    public function getVtexOrderId()
    {
        return $this->getData(self::VTEX_ORDER_ID);
    }

    /**
     * Return identities
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Set Title
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }


    /**
     * Set Title
     *
     * @param string $order_id
     * @return $this
     */
    public function setOrderId($order_id)
    {
        return $this->setData(self::ORDER_ID, $order_id);
    }

    /**
     * Set Content
     *
     * @param string $vtexOrderId
     * @return $this
     */
    public function setVtexOrderId($vtexOrderId)
    {
        return $this->setData(self::VTEX_ORDER_ID, $vtexOrderId);
    }
}
