<?php

namespace Vtex\VtexMagento\Api\Data;

interface VtexOrdersInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID           = 'id';
    const ORDER_ID           = 'order_id';
    const VTEX_ORDER_ID           = 'vtex_order_id';
    /**#@-*/


    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getOrderId();

    /**
     * @return mixed
     */
    public function getVtexOrderId();

    /**
     * @param $id
     * @return mixed
     */
    public function setId($id);


    /**
     * @param $order_id
     * @return mixed
     */
    public function setOrderId($order_id);

    /**
     * @param $vtexOrderId
     * @return mixed
     */
    public function setVtexOrderId($vtexOrderId);
}
