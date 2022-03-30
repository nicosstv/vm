<?php

namespace Vtex\VtexMagento\Model;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;
use Vtex\VtexMagento\Api\Data\ImportInterface;

/**
 * Class File
 * @package Toptal\Blog\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Import extends AbstractModel implements ImportInterface, IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'vtex_import';

    /**
     * Post Initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vtex\VtexMagento\Model\ResourceModel\Import');
    }

    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function getTotal()
    {
        return $this->getData(self::TOTAL);
    }

    public function getProgress()
    {
        return $this->getData(self::PROGRESS);
    }

    public function getErrors()
    {
        return $this->getData(self::ERRORS);
    }

    public function getFilename()
    {
        return $this->getData(self::FILENAME);
    }

    public function getDate()
    {
        return $this->getData(self::DATE);
    }

    /**
     * Return identities
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function setTotal($total)
    {
        return $this->setData(self::TOTAL, $total);
    }

    public function setProgress($progress)
    {
        return $this->setData(self::PROGRESS, $progress);
    }

    public function setErrors($errors)
    {
        return $this->setData(self::ERRORS, $errors);
    }

    public function setFilename($filename)
    {
        return $this->setData(self::FILENAME, $filename);
    }

    public function setDate($date)
    {
        return $this->setData(self::DATE, $date);
    }
}
