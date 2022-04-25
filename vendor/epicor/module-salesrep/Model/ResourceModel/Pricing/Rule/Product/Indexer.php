<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\Product;


class Indexer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\CollectionFactory
     */
    protected $salesRepResourcePricingRuleCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $_productIds;

    /**
     * Limitation for products collection
     *
     * @var int|array|null
     */
    protected $_productsFilter = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $_resourceIterator;



    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\CollectionFactory $salesRepResourcePricingRuleCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory $collectionFactory,
        $connectionName = null
    ) {
        $this->salesRepResourcePricingRuleCollectionFactory = $salesRepResourcePricingRuleCollectionFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->_resourceIterator = $resourceIterator;
        parent::__construct(
            $context,
            $connectionName
        );
    }


    protected function _construct()
    {
        $this->_init('ecc_salesrep_pricing_rule_product', 'id');
    }

    public function _getWriteAdapter()
    {
        return $this->getConnection();
    }
    public function truncate()
    {
        $this->_getWriteAdapter()->truncateTable($this->getTable('ecc_salesrep_pricing_rule_product_idx'));
    }

    public function invalidateIndex($ruleId = null, $productId = null)
    {
        $table = $this->getTable('ecc_salesrep_pricing_rule_product');

        $adapter = $this->_getWriteAdapter();
        /* @var $adapter Magento_Db_Adapter_Pdo_Mysql */

        $where = $ruleId ? 'pricing_rule_id = ' . $ruleId : '';
        $rowCount = $adapter->update($table, array('is_valid' => 0), $where);
    }

    public function deleteProductRules($productId)
    {
        $table = $this->getTable('ecc_salesrep_pricing_rule_product');

        $adapter = $this->_getWriteAdapter();
        /* @var $adapter Magento_Db_Adapter_Pdo_Mysql */

        $rowCount = $adapter->delete($table, 'product_id = ' . $productId);
    }

    public function deleteInvalid($ruleId = null, $productId = null)
    {
        $table = $this->getTable('ecc_salesrep_pricing_rule_product');

        $adapter = $this->_getWriteAdapter();
        /* @var $adapter Magento_Db_Adapter_Pdo_Mysql */

        $where = $ruleId ? 'pricing_rule_id = ' . $ruleId . ' AND is_valid = 0' : 'is_valid = 0';
        $rowCount = $adapter->delete($table, $where);
    }

    public function getMatchingProductIds($rule)
    {
        //if ($this->_productIds === null) {
            $this->_productIds = [];
            $rule->setCollectedAttributes([]);

            //if ($rule->getWebsiteIds()) {
                /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
                $productCollection = $this->_productCollectionFactory->create();
              //  $productCollection->addWebsiteFilter($rule->getWebsiteIds());
                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }
                $rule->getConditions()->collectValidatedAttributes($productCollection);

                $this->_resourceIterator->walk(
                    $productCollection->getSelect(),
                    [[$this, 'callbackValidateProduct']],
                    [
                        'attributes' => $rule->getCollectedAttributes(),
                        'rule' => $rule,
                        'product' => $this->_productFactory->create()
                    ]
                );
           // }
        //}

        return $this->_productIds;
    }
    
    
    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $websites = $this->_getWebsitesMap();
        $results = [];

        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            if($args['rule']->getConditions()->validate($product)){
                $this->_productIds[] = $product->getId();
            }
        }
        
    }
    
    
    /**
     * Prepare website map
     *
     * @return array
     */
    protected function _getWebsitesMap()
    {
        $map = [];
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            // Continue if website has no store to be able to create catalog rule for website without store
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }
    
    public function reIndex($ruleId = null)
    {
        // loop through all rules, get product ids for each rule
        $collection = $this->salesRepResourcePricingRuleCollectionFactory->create();
        /* @var $collection Epicor_SalesRep_Model_Resource_Pricing_Rule_Collection */
        $collection->addFieldToFilter('is_active', 1);
        $collection->addOrder('sales_rep_account_id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        if ($ruleId) {
            $collection->addFieldToFilter('id', $ruleId);
        }

        $batch = 1000;
        $counter = 0;
        $data = array();

        foreach ($collection->getItems() as $rule) {
            foreach ($this->storeManager->getStores() as $storeId => $store) {
                try {
                    /* @var $rule Epicor_SalesRep_Model_Pricing_Rule */
                    $innerSelect = $this->_innerIndexSelect($rule, $storeId);

                    $productIds = $this->getMatchingProductIds($rule);
                     
              
        
//                    $condSql = $rule->getConditions()->prepareConditionSql();
//                    $ruleQuery = $this->_getWriteAdapter()->select();
//                    $ruleQuery->from(
//                        array('cpf' => $innerSelect), array(new \Zend_Db_Expr('DISTINCT cpf.entity_id'))
//                    );
//                    $ruleQuery->joinLeft(
//                        array('ccp' => $this->getTable('catalog_category_product')), 'ccp.product_id = cpf.entity_id', array()
//                    );
//                    if (!empty($condSql)) {
//                        $ruleQuery->where($condSql);
//                    }
//
//                    $query = $this->_getWriteAdapter()->query($ruleQuery);

                    foreach($productIds as $pid) {
                        $data[] = array(
                            'pricing_rule_id' => $rule->getId(),
                            'product_id' => $pid,
                            'is_valid' => 1,
                            'store_id' => $storeId
                        );

                        $counter++;

                        if ($counter == $batch) {
                            $counter = 1;
                            $this->_insertData($data);
                            $data = array();
                        }
                    }

                    //$query->closeCursor();

                    if (!empty($data)) {
                        $this->_insertData($data);
                        $data = array();
                    }

//                    unset($condSql);
//                    unset($ruleQuery);
//                    unset($query);
                } catch (\Exception $e) {
                    
                     
                    $this->logger->critical($e);
                    $this->logger->log(200, 'ERROR WITH RULE '.$rule->getId().' (SALES REP ACCOUNT: '.$rule->getSalesRepAccountId().')'.$e->getMessage());
                }
            }
            unset($rule);
        }
    }

    private function _innerIndexSelect(\Epicor\SalesRep\Model\Pricing\Rule $rule, $storeId = 0)
    {
        $types = $this->_getAttributesType($rule);
        if (count($types) > 0) {
            $tables = array();
            foreach ($types as $type => $attributes) {
                $tables[] = (string) $this->_getAttributeTableSelect($type, $attributes, $storeId);
            }
            $tablesSelect = new \Zend_Db_Expr('(' . join(' UNION ALL ', $tables) . ')');
            $innerSelect = $this->_getReadAdapter()->select();

            $columns = $this->_getColumns($types);
            $innerSelect->from(array('cpf' => $this->getTable('catalog_product_entity')), '*');
            $innerSelect->joinLeft(array('atts' => $tablesSelect), 'cpf.entity_id = atts.entity_id', $columns);
            $innerSelect->group('atts.entity_id');
        } else {
            $innerSelect = $this->getTable('catalog_product_entity');
        }

        return $innerSelect;
    }

    private function _getAttributesType(\Epicor\SalesRep\Model\Pricing\Rule $rule)
    {
        $attributes = array();
        foreach ($rule->getConditions()->getConditions() as $condition) {
            $attribute = $condition->getAttribute();
            $attributes[] = $attribute;
        }

        $types = array();
        if (count($attributes) > 0) {

            //M1 > M2 Translation Begin (Rule p2-1)
            //$collection = Mage::getModel('catalog/entity_attribute');
            $collection = $this->collectionFactory->create();
            //M1 > M2 Translation End

            /* @var $collection Mage_Catalog_Model_Resource_Attribute_Collection */
            $collection->addFieldToFilter('attribute_code', $attributes);
            //$collection->addFieldToFilter('is_used_for_promo_rules', true);

            foreach ($collection as $attribute) {
                if ($attribute->getBackendType() != 'static') {
                    $types[$attribute->getBackendType()][$attribute->getId()] = $attribute->getAttributeCode();
                }
            }
        }

        return $types;
    }

    private function _getColumns($types)
    {
        $columns = array();
        foreach ($types as $type => $attributes) {
            foreach ($attributes as $attributeId => $attributeCode) {
                $columns[$attributeCode] = new \Zend_Db_Expr("GROUP_CONCAT(IF(attribute_id = $attributeId, value, '') SEPARATOR '')");
            }
        }

        return $columns;
    }

    private function _getAttributeTableSelect($type, $attributes, $store_id)
    {
        $tableName = $this->getTable('catalog_product_entity_' . $type);

        $select = $this->_getReadAdapter()->select();
        $select->from(array('o' => $tableName), array('entity_id', 'attribute_id', 'value'));
        $select->where('attribute_id', array_keys($attributes));
        $select->where("store_id = (SELECT MAX(store_id) FROM $tableName i WHERE i.entity_id = o.entity_id AND i.attribute_id = o.attribute_id AND i.store_id IN (0, $store_id))");

        return $select;
    }

    private function _insertData($data)
    {
        $adapter = $this->_getWriteAdapter();
        /* @var $adapter Magento_Db_Adapter_Pdo_Mysql */
        $adapter->insertOnDuplicate($this->getMainTable(), $data, array('is_valid' => 1));
    }

}
