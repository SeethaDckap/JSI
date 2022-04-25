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
class ListModel extends \Epicor\Database\Model\ResourceModel\Lists
{


    const TEMPORARY_TABLE_PREFIX = 'lists_tmp_';

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
        parent::__construct($context,$connectionName);
    }

    /**
     * Apply List Filter.
     *
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * @param array $productArray
     * @return \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     */
    public function applyListFilter(
        \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection,
        $productArray
    )
    {
        $tmpTable = $this->createTemporaryTable();
        $this->getConnection()->insertArray($tmpTable->getName(), [self::FIELD_ENTITY_ID], $productArray);
        $collection->getSelect()->joinInner(
            ['liststmp' => $tmpTable->getName()],
            'e.entity_id = liststmp.' . self::FIELD_ENTITY_ID,
            []);

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
            self::FIELD_ENTITY_ID,
            Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        );
        $table->setOption('type', 'memory');
        $connection->createTemporaryTable($table);
        return $table;
    }
}
