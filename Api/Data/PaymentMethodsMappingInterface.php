<?php

namespace Vtex\VtexMagento\Api\Data;

interface PaymentMethodsMappingInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const VTEX_ID = 'vtex_id';
    const VTEX_NAME = 'vtex_name';
    const VTEX_GROUP_NAME = 'vtex_group_name';
    const MAGENTO_ID = 'magento_id';
    const MAGENTO_NAME = 'magento_name';
    const MAGENTO_GROUP_NAME = 'magento_group_name';
    /**#@-*/


    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getVtexId();

    /**
     * @return mixed
     */
    public function getVtexName();

    /**
     * @return mixed
     */
    public function getVtexGroupName();

    /**
     * @return mixed
     */
    public function getMagentoId();

    /**
     * @return mixed
     */
    public function getMagentoName();

    /**
     * @return mixed
     */
    public function getMagentoGroupName();

    /**
     * @param $id
     * @return mixed
     */
    public function setId($id);

    /**
     * @param $vtex_id
     * @return mixed
     */
    public function setVtexId($vtex_id);

    /**
     * @param $vtex_name
     * @return mixed
     */
    public function setVtexName($vtex_name);

    /**
     * @param $vtex_group_name
     * @return mixed
     */
    public function setVtexGroupName($vtex_group_name);

    /**
     * @param $magento_id
     * @return mixed
     */
    public function setMagentoId($magento_id);

    /**
     * @param $magento_name
     * @return mixed
     */
    public function setMagentoName($magento_name);

    /**
     * @param $magento_group_name
     * @return mixed
     */
    public function setMagentoGroupName($magento_group_name);

    /**
     * @param $vtex_group_name
     * @return mixed
     */
    public function getByName($vtex_group_name);
}
