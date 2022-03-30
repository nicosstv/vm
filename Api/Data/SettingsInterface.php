<?php

namespace Vtex\VtexMagento\Api\Data;

interface SettingsInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SETTINGS_ID           = 'settings_id';
    const VENDOR_NAME           = 'vendor_name';
    const APP_KEY               = 'app_key';
    const APP_TOKEN             = 'app_token';
    const SELLER_ID             = 'seller_id';
    const CATALOG_V2             = 'catalog_v2';
    const VTEX_AUT_COOKIE       = 'vtex_aut_cookie';
    const SALES_CHANNEL         = 'salesChannel';
    /**#@-*/


    /**
     * @return mixed
     */
    public function getSettingsId();

    /**
     * @return mixed
     */
    public function getVendorName();

    /**
     * @return mixed
     */
    public function getAppKey();

    /**
     * @return mixed
     */
    public function getAppToken();

    /**
     * @return mixed
     */
    public function getSellerId();

    /**
     * @return mixed
     */
    public function getVtexAutCookie();

    /**
     * @return mixed
     */
    public function getSalesChannel();

    /**
     * @return mixed
     */
    public function getCatalogV2();

    /**
     * @param $vendor_name
     * @return mixed
     */
    public function setVendorName($vendor_name);

    /**
     * @param $app_key
     * @return mixed
     */
    public function setAppKey($app_key);

    /**
     * @param $app_token
     * @return mixed
     */
    public function setAppToken($app_token);

    /**
     * @param $seller_id
     * @return mixed
     */
    public function setSellerId($seller_id);

    /**
     * @param $id
     * @return mixed
     */
    public function setSettingsId($id);

    /**
     * @param $vtex_aut_cookie
     * @return mixed
     */
    public function setVtexAutCookie($vtex_aut_cookie);

    /**
     * @param $salesChannel
     * @return mixed
     */
    public function setSalesChannel($salesChannel);

    /**
     * @param $catalog_v2
     * @return mixed
     */
    public function setCatalogV2($catalog_v2);
}
