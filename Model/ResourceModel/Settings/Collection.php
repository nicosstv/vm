<?php
namespace Vtex\VtexMagento\Model\ResourceModel\Settings;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'settings_id';
    protected $_isPkAutoIncrement = false;


    protected function _construct()
    {
        $this->_init('Vtex\VtexMagento\Model\Settings', 'Vtex\VtexMagento\Model\ResourceModel\Settings');
    }
}
