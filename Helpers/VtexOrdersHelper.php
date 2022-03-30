<?php


namespace Vtex\VtexMagento\Helpers;


use Magento\Framework\App\ObjectManager;

class VtexOrdersHelper
{

    /**
     * @param $vtex_order_id
     * @return int
     */
    public function getOrderId($vtex_order_id)
    {
        $objectManager = ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('vtex_orders');


        $sql = $connection->select()
            ->from($tableName, 'order_id')
            ->where('vtex_order_id = ?', $vtex_order_id)
            ->limit(1);
        $result = $connection->fetchAll($sql);

        return ($result && $result[0]['order_id']) ? $result[0]['order_id'] : 0;
    }

    /**
     * @param $order_id
     * @return int
     */
    public function getVtexOrderId($order_id)
    {
        $objectManager = ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('vtex_orders');


        $sql = $connection->select()
            ->from($tableName, 'vtex_order_id')
            ->where('order_id = ?', $order_id)
            ->limit(1);
        $result = $connection->fetchAll($sql);

        return ($result && $result[0]['vtex_order_id']) ? $result[0]['vtex_order_id'] : 0;
    }

}
