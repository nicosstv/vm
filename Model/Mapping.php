<?php

namespace Vtex\VtexMagento\Model;

use Magento\Framework\App\ObjectManager;
use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;
use Vtex\VtexMagento\Api\Data\MappingInterface;

/**
 * Class File
 * @package Toptal\Blog\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mapping extends AbstractModel implements MappingInterface, IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'vtex_categories_mapping';

    /**
     * Post Initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vtex\VtexMagento\Model\ResourceModel\Mapping');
    }

    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
    }

    public function getVtexId()
    {
        return $this->getData(self::VTEX_ID);
    }

    public function getVtexName()
    {
        return $this->getData(self::VTEX_NAME);
    }

    public function getMagentoId()
    {
        return $this->getData(self::MAGENTO_ID);
    }

    public function getMagentoName()
    {
        return $this->getData(self::MAGENTO_NAME);
    }

    /**
     * Return identities
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setParentId($parent_id)
    {
        return $this->setData(self::PARENT_ID, $parent_id);
    }

    public function setVtexId($vtex_id)
    {
        return $this->setData(self::VTEX_ID, $vtex_id);
    }

    public function setVtexName($vtex_name)
    {
        return $this->setData(self::VTEX_NAME, $vtex_name);
    }

    public function setMagentoId($magento_id)
    {
        return $this->setData(self::MAGENTO_ID, $magento_id);
    }

    public function setMagentoName($magento_name)
    {
        return $this->setData(self::MAGENTO_NAME, $magento_name);
    }

    public function insertRecursive($item, $parentId = null)
    {
        $objectManager = ObjectManager::getInstance();
        $model = $objectManager
            ->create('Vtex\VtexMagento\Model\ResourceModel\Mapping\Collection')
            ->addFieldToFilter('vtex_id', $item['value']['id'])
            ->getFirstItem();

        if (!$model->getId()) {
            $model = $this->setData([
                'vtex_id' => $item['value']['id'],
                'vtex_name' => $item['value']['name'],
                'parent_id' => $parentId
            ])->save();
        }

        foreach ($item['children'] as $child) {
            $this->insertRecursive($child, $model->getId());
        }
    }
}
