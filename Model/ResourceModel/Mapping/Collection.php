<?php
namespace Vtex\VtexMagento\Model\ResourceModel\Mapping;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    protected $_isPkAutoIncrement = true;


    protected function _construct()
    {
        $this->_init('Vtex\VtexMagento\Model\Mapping', 'Vtex\VtexMagento\Model\ResourceModel\Mapping');
    }
}
