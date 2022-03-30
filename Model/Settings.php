<?php

namespace Vtex\VtexMagento\Model;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;
use \Vtex\VtexMagento\Api\Data\SettingsInterface;

/**
 * Class File
 * @package Toptal\Blog\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Settings extends AbstractModel implements SettingsInterface, IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'vtex_settings';

    /**
     * Post Initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vtex\VtexMagento\Model\ResourceModel\Settings');
    }


    /**
     * Get Title
     *
     * @return string|null
     */
    public function getVendorName()
    {
        return $this->getData(self::VENDOR_NAME);
    }

    /**
     * Get Content
     *
     * @return string|null
     */
    public function getAppKey()
    {
        return $this->getData(self::APP_KEY);
    }

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getAppToken()
    {
        return $this->getData(self::APP_TOKEN);
    }

    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getSettingsId()
    {
        return $this->getData(self::SETTINGS_ID);
    }

    /**
     *  Get VTEX aut cookie //vtex_aut_cookie
     */

    public function getVtexAutCookie()
    {
        return $this->getData(self::VTEX_AUT_COOKIE);
    }

    /**
     *  Get Sales Channel
     */

    public function getSalesChannel()
    {
        return $this->getData(self::SALES_CHANNEL);
    }

    /**
     * Get Catalog Version
     *
     * @return int
     */
    public function getCatalogV2()
    {
        return $this->getData(self::CATALOG_V2);
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
     * @param string $app_key
     * @return $this
     */
    public function setAppKey($app_key)
    {
        return $this->setData(self::APP_KEY, $app_key);
    }

    /**
     * Set Content
     *
     * @param string $app_token
     * @return $this
     */
    public function setAppToken($app_token)
    {
        return $this->setData(self::APP_TOKEN, $app_token);
    }

    /**
     * Set Created At
     *
     * @param string $vendor_name
     * @return $this
     */
    public function setVendorName($vendor_name)
    {
        return $this->setData(self::VENDOR_NAME, $vendor_name);
    }

    /**
     * Set Created At
     *
     * @param string $vtex_aut_cookie
     * @return $this
     */
    public function setVtexAutCookie($vtex_aut_cookie)
    {
        return $this->setData(self::VTEX_AUT_COOKIE, $vtex_aut_cookie);
    }

    /**
     * Set Sales Channel
     *
     * @param string $salesChannel
     * @return $this
     */
    public function setSalesChannel($salesChannel)
    {
        return $this->setData(self::SALES_CHANNEL, $salesChannel);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setSettingsId($id)
    {
        return $this->setData(self::SETTINGS_ID, $id);
    }

    public function setSellerId($seller_id)
    {
        return $this->setData(self::SELLER_ID, $seller_id);
    }

    public function setCatalogV2($catalog_v2)
    {
        return $this->setData(self::CATALOG_V2, $catalog_v2);
    }
}
