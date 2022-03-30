<?php

namespace Vtex\VtexMagento\Model;

use Magento\Framework\App\ObjectManager;
use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;
use Vtex\VtexMagento\Api\Data\PaymentMethodsMappingInterface;

/**
 * Class File
 * @package Toptal\Blog\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentMethodsMapping extends AbstractModel implements PaymentMethodsMappingInterface, IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'vtex_payment_methods_mapping';

    /**
     * Post Initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vtex\VtexMagento\Model\ResourceModel\PaymentMethodsMapping');
    }

    public function getVtexId()
    {
        return $this->getData(self::VTEX_ID);
    }

    public function getVtexName()
    {
        return $this->getData(self::VTEX_NAME);
    }

    public function getVtexGroupName()
    {
        return $this->getData(self::VTEX_GROUP_NAME);
    }

    public function getMagentoId()
    {
        return $this->getData(self::MAGENTO_ID);
    }

    public function getMagentoName()
    {
        return $this->getData(self::MAGENTO_NAME);
    }

    public function getMagentoGroupName()
    {
        return $this->getData(self::MAGENTO_GROUP_NAME);
    }

    /**
     * Return identities
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setVtexId($vtex_id)
    {
        return $this->setData(self::VTEX_ID, $vtex_id);
    }

    public function setVtexName($vtex_name)
    {
        return $this->setData(self::VTEX_NAME, $vtex_name);
    }

    public function setVtexGroupName($vtex_group_name)
    {
        return $this->setData(self::VTEX_GROUP_NAME, $vtex_group_name);
    }

    public function setMagentoId($magento_id)
    {
        return $this->setData(self::MAGENTO_ID, $magento_id);
    }

    public function setMagentoName($magento_name)
    {
        return $this->setData(self::MAGENTO_NAME, $magento_name);
    }

    public function setMagentoGroupName($magento_group_name)
    {
        return $this->setData(self::MAGENTO_GROUP_NAME, $magento_group_name);
    }

    public function getByName($name) {
        $objectManager = ObjectManager::getInstance();
        $model = $objectManager
            ->create('Vtex\VtexMagento\Model\ResourceModel\Mapping\PaymentMethodsCollection')
            ->addFieldToFilter('vtex_group_name', str_replace('PaymentGroup', '', $name))
            ->getFirstItem();
        return $model ? $model->getMagentoGroupName(): null;
    }

    public function insert($item)
    {
        $objectManager = ObjectManager::getInstance();
        $model = $objectManager
            ->create('Vtex\VtexMagento\Model\ResourceModel\Mapping\PaymentMethodsCollection')
            ->addFieldToFilter('vtex_id', $item['id'])
            ->getFirstItem();

        if (!$model->getId()) {
            $model = $this->setData([
                'vtex_id' => $item['id'],
                'vtex_name' => $item['name'],
                'vtex_group_name' => $item['groupName']
            ])->save();
        }
    }
}
