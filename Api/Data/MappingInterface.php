<?php

namespace Vtex\VtexMagento\Api\Data;

interface MappingInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const PARENT_ID = 'parent_id';
    const VTEX_ID = 'vtex_id';
    const VTEX_NAME = 'vtex_name';
    const MAGENTO_ID = 'magento_id';
    const MAGENTO_NAME = 'magento_name';
    /**#@-*/


    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getParentId();

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
    public function getMagentoId();

    /**
     * @return mixed
     */
    public function getMagentoName();

    /**
     * @param $id
     * @return mixed
     */
    public function setId($id);


    /**
     * @param $parent_id
     * @return mixed
     */
    public function setParentId($parent_id);

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
     * @param $magento_id
     * @return mixed
     */
    public function setMagentoId($magento_id);

    /**
     * @param $magento_name
     * @return mixed
     */
    public function setMagentoName($magento_name);
}
