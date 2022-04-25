<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Model\ResourceModel;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Ddl\Table;

/**
 * Model Resource Class for List
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class NewListgrid extends \Epicor\Database\Model\ResourceModel\Lists
{


    const TEMPORARY_TABLE_PREFIX = 'lists_product_tmp_';

    const FIELD_ENTITY_ID = 'lists_entity_id';

    /**
     * DeploymentConfig
     *
     * @var DeploymentConfig
     */
    private $config;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\DeploymentConfig $config Config.
     */
    public function __construct(
        DeploymentConfig $config,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    )
    {
        $this->config = $config;
        parent::__construct($context, $connectionName);
    }

    /**
     * Apply List Filter.
     *
     * @param \Epicor\Lists\Model\ResourceModel\Product\Collection $collection
     * @param array $productArray
     */
    public function applyListFilter(
        \Epicor\Lists\Model\ResourceModel\Product\Collection $collection,
        $productArray
    )
    {
        $tmpTable = $this->createTemporaryTable();
        $this->getConnection()->insertMultiple($tmpTable->getName(), $productArray);
        $collection->getSelect()->joinLeft(
            ['lp' => $tmpTable->getName()],
            'e.sku = lp.sku',
            ['qty', 'location_code']
        );

        return $collection;
    }

    /**
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createTemporaryTable()
    {
        $connection = $this->getConnection();
        $tableName = $this->_resources->getTableName(str_replace('.', '_', uniqid(self::TEMPORARY_TABLE_PREFIX, true)));
        $table = $connection->newTable($tableName);
        if ($this->config->get('db/connection/indexer/persistent')) {
            $connection->dropTemporaryTable($table->getName());
        }
        $table->addColumn(
            'sku',
            Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'SKU'
        );

        $table->addColumn(
            'qty',
            Table::TYPE_FLOAT,
            255,
            ['nullable' => false],
            'QTY'
        );

        $table->addColumn(
            'location_code',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Location Code'
        );
        $table->setOption('type', 'memory');
        $connection->createTemporaryTable($table);
        return $table;
    }
}
