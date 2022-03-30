<?php

namespace Vtex\VtexMagento\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PaymentMethodsMapping extends AbstractDb
{
    protected $_isPkAutoIncrement = true;
    /**
     * Post Abstract Resource Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vtex_payment_methods_mapping', 'id');
    }
}
