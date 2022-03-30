<?php

namespace Vtex\VtexMagento\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Settings extends AbstractDb
{
    protected $_isPkAutoIncrement = false;
    /**
     * Post Abstract Resource Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vtex_settings', 'settings_id');
    }
}
