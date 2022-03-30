<?php

namespace Vtex\VtexMagento\Setup;

use \Magento\Framework\Setup\InstallSchemaInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 *
 * @package Toptal\Blog\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Install Blog Posts table
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $tableName = $setup->getTable('vtex_settings');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'settings_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'vendor_name',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Vendor Name'
                )
                ->addColumn(
                    'app_key',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'App Key'
                )->addColumn(
                    'app_token',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'App Token'
                )->addColumn(
                    'seller_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Seller ID'
                )->addColumn(
                    'catalog_v2',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false],
                    'Use catalog V2'
                )->addColumn(
                    'vtex_aut_cookie',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'VtexIdClientAutCookie'
                )->addColumn(
                    'salesChannel',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Sales Channel'
                )
                ->setComment('VTEX Settings');
            $setup->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('vtex_orders');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Magento order ID'
                )
                ->addColumn(
                    'vtex_order_id',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'VTEX order ID'
                )
                ->setComment('VTEX Orders');
            $setup->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('vtex_import');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Import type'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Import status'
                )
                ->addColumn(
                    'total',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Import total entries count'
                )
                ->addColumn(
                    'progress',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Import progress count'
                )
                ->addColumn(
                    'errors',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Import errors count'
                )
                ->addColumn(
                    'filename',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Import log file'
                )
                ->addColumn(
                    'date',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Import log date'
                )
                ->setComment('VTEX Orders');
            $setup->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('vtex_payment_methods_mapping');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'vtex_id',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'VTEX Payment Method ID'
                )
                ->addColumn(
                    'vtex_name',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'VTEX Payment Method Name'
                )
                ->addColumn(
                    'vtex_group_name',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'VTEX Payment Method Group Name'
                )
                ->addColumn(
                    'magento_id',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Magento Payment Method ID'
                )
                ->addColumn(
                    'magento_name',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Magento Payment Method Name'
                )
                ->addColumn(
                    'magento_group_name',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Magento Payment Method Group Name'
                )
                ->setComment('VTEX Payment Methods Mapping');
            $setup->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('vtex_categories_mapping');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'parent_id',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Parent ID'
                )
                ->addColumn(
                    'vtex_id',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'VTEX Category ID'
                )
                ->addColumn(
                    'vtex_name',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'VTEX Category Name'
                )
                ->addColumn(
                    'magento_id',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Magento Category ID'
                )
                ->addColumn(
                    'magento_name',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Magento Category Name'
                )
                ->setComment('VTEX Categories Mapping');
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
