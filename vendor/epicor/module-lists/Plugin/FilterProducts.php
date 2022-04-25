<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Plugin;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;

class FilterProducts
{

    const TEMPORARY_TABLE_PREFIX = 'lists_tmp_';

    const FIELD_ENTITY_ID = 'lists_entity_id';
    /**
     * @var \Epicor\Comm\Helper\Locations 
     */
    protected $commLocationsHelper;
    
    /**
     * @var \Magento\Framework\Registry 
     */
    protected $registry;
    
    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    protected $listsFrontendProductHelper;
    
    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var DeploymentConfig
     */
    private $config;

    public function __construct(
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        DeploymentConfig $config = null
    )
    {
        $this->commLocationsHelper = $commLocationsHelper;
        $this->registry = $registry;
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->resource = $resource;
        $this->config = $config !== null ? $config : ObjectManager::getInstance()->get(DeploymentConfig::class);
    }

    /**
     * Apply list & Location filtering before collection load
     * 
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * @param boolean $printQuery
     * @param boolean $logQuery
     * 
     * @return array
     */
    public function beforeLoad(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        $printQuery = false,
        $logQuery = false
    )
    {
        $this->applyLocationFilter($collection);
        $this->applyListFilter($collection);
       
        return [$printQuery, $logQuery];
    }

    
 

    /**
     * Applies location filtering if locations is enabled
     * 
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * 
     * @return void
     */
    public function applyLocationFilter(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        if ($this->commLocationsHelper->isLocationsEnabled() == false ||
            $collection->getFlag('no_product_filtering') ||
            $collection instanceof \Magento\Bundle\Model\ResourceModel\Selection\Collection ||
            $collection->getFlag('no_location_filtering') ||
            $collection->getFlag('location_sql_applied')
        ) {
            return;
        }

        $locationTable = $collection->getTable('ecc_location_product');
        $locationString = $this->commLocationsHelper->getEscapedCustomerDisplayLocationCodes();
        $configurableTypeCode = \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
        $isLocationRequireForConfigurable = $this->commLocationsHelper->isLocationRequireForConfigurable();

        if (!$isLocationRequireForConfigurable) {
            $collection->joinTable(['locations' => $locationTable], 'product_id = entity_id', ['location_code'], null,
                "left");
            $collection->getSelect()->where('(locations.location_code in (' . $locationString . ') or e.type_id = "' . $configurableTypeCode . '")');
        } else {
            $collection->joinTable(['locations' => $locationTable], 'product_id = entity_id', ['location_code']);
            $collection->getSelect()->where('location_code in (' . $locationString . ')');
        }
        $collection->groupByAttribute('entity_id');
        $collection->setFlag('location_sql_applied', true);
        $this->registry->unregister('location_sql_applied');
        $this->registry->register('location_sql_applied', true);
    }

    /**
     * Applies lists filtering if lists is enabled
     * 
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * 
     * @return void
     */
    private function applyListFilter(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        if (
            $this->listsFrontendProductHelper->listsDisabled() ||
            $collection->getFlag('no_product_filtering') ||
            $collection instanceof \Magento\Bundle\Model\ResourceModel\Selection\Collection ||
            $collection->getFlag('lists_sql_applied')
        ) {
            return;
        }
        
        if ($this->listsFrontendProductHelper->hasFilterableLists() || $this->listsFrontendContractHelper->mustFilterByContract()) {
            $productIds = $this->listsFrontendProductHelper->getActiveListsProductIds();
         //   $collection->addFieldToFilter('e.entity_id', ['in' => explode(',',$productIds)]);
            // The below is needed for filtering to work on search page (doesnt seem to conflict with above!)
        
            if($productIds){ 
//                $collection->getSelect()->where(
//                    '(e.entity_id IN(' . $productIds . '))'
//                );               
                
                $collection->setFlag('lists_sql_applied', true);
                $table = $this->createTemporaryTable();
                $productIdsd = explode(',',$productIds);
                $new_array=false;
                foreach($productIdsd as $val)
                { 
                     $new_array[] = array($val);
                }
                $this->getConnection()->insertArray($table->getName(),array(self::FIELD_ENTITY_ID),$new_array);
                $collection->getSelect()->joinInner(
                ['liststmp'=>$table->getName()],
                'e.entity_id = liststmp.'.self::FIELD_ENTITY_ID,
                []);
            }else{
                $collection->getSelect()->where(
                    '(e.entity_id IN(null))'
                );
            }
            $collection->setFlag('lists_sql_applied', true);
        }
        
    }

    /**
     * @return false|AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
    /**
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createTemporaryTable()
    {
        $connection = $this->getConnection();
        $tableName = $this->resource->getTableName(str_replace('.', '_', uniqid(self::TEMPORARY_TABLE_PREFIX, true)));
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
