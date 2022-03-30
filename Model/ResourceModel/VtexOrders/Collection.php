<?php
namespace Vtex\VtexMagento\Model\ResourceModel\VtexOrders;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    protected $_isPkAutoIncrement = false;


    protected function _construct()
    {
        $this->_init('Vtex\VtexMagento\Model\VtexOrders', 'Vtex\VtexMagento\Model\ResourceModel\VtexOrders');
    }
}
