<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ResourceModel\ListModel\Product;


/**
 * Model Resource Class for List Product Price
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Conditions extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

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
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $resourceIterator;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    protected $_productIds = [];

    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    protected $sqlBuilder;


    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \Epicor\Comm\Model\Condition\Sql\Builder $sqlBuilder,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        $connectionName = null
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->productFactory = $productFactory;
        $this->sqlBuilder = $sqlBuilder;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resourceIterator = $resourceIterator;

        parent::__construct(
            $context,
            $connectionName
        );
    }


    protected function _construct()
    {
        $this->_init('epicor_lists/listModel', 'id');
    }


    public function prepareConditionSql($conditionsModel)
    {
        $wheres = array();
         //echo '<pre>';     print_r($conditionsModel->asArray()); echo 'fff'; exit;
        foreach ($conditionsModel->asArray() as $condition) {
            /** @var $condition Mage_Rule_Model_Condition_Abstract */
            //print_r($condition); echo 'fff';
        //    $wheres[] = $this->prepareConditionSql($condition);
        }
        $wheres[] = '`cpf`.`sku` LIKE \'%103%\'';
        if (empty($wheres)) {
            return '';
        }
        $delimiter = $conditionsModel->getAggregator() == "all" ? ' AND ' : ' OR ';
        return ' (' . implode($delimiter, $wheres) . ') ';
    }


    /**
     *
     * @param \Epicor\Lists\Model\ListModel\Product\Conditions $conditionsModel
     */
    public function buildSql($conditionsModel)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $innerSelect = $this->_innerIndexSelect($conditionsModel, $storeId);

         //   $condSql = $this->prepareConditionSql($conditionsModel->getConditions());

            $collection = $this->productCollectionFactory->create();
            $condSql =  $this->sqlBuilder->attachConditionToCollectionList($collection,$conditionsModel->getConditions());


            $ruleQuery = $this->getConnection('read')->select();
            $ruleQuery->from(
                array('cpf' => $innerSelect), array(new \Zend_Db_Expr('DISTINCT cpf.entity_id'))
            );

            if (!empty($condSql)) {
                $ruleQuery->where($condSql);
            }
             return (string) $ruleQuery;
        } catch (\Exception $e) {
            echo $e->getMessage();
            die();
            return false;
        }
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

    private function _innerIndexSelect(\Epicor\Lists\Model\ListModel\Product\Conditions $conditionsModel, $storeId = 0)
    {
        $types = $this->_getAttributesType($conditionsModel);
        if (count($types) > 0) {
            $tables = array();
            foreach ($types as $type => $attributes) {
                $tables[] = (string) $this->_getAttributeTableSelect($type, $attributes, $storeId);
            }
            $tablesSelect = new \Zend_Db_Expr('(' . join(' UNION ALL ', $tables) . ')');
            $innerSelect = $this->getConnection('read')->select();

            $columns = $this->_getColumns($types);
            $innerSelect->from(array('cpf' => $this->getTable('catalog_product_entity')), '*');
            $innerSelect->joinLeft(array('atts' => $tablesSelect), 'cpf.entity_id = atts.entity_id', $columns);
            $innerSelect->group('atts.entity_id');
        } else {
            $innerSelect = $this->getTable('catalog_product_entity');
        }

        return $innerSelect;
    }

    private function _getAttributesType(\Epicor\Lists\Model\ListModel\Product\Conditions $conditionsModel)
    {
        $attributes = array();
        foreach ($conditionsModel->getConditions()->getConditions() as $condition) {
         //  echo '<pre>';  echo $condition->getAttributeObject()->getAttributeCode() ; print_r(get_class_methods($condition));exit;
            if($condition->getAttributeObject()){
                $attribute = $condition->getAttributeObject()->getAttributeCode();
                $attributes[] = $attribute;
            }
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

        $select = $this->getConnection('read')->select();
        $select->from(array('o' => $tableName), array('entity_id', 'attribute_id', 'value'));
        $select->where('attribute_id IN (?)', array_keys($attributes));
        $select->where("store_id = (SELECT MAX(store_id) FROM $tableName i WHERE i.entity_id = o.entity_id AND i.attribute_id = o.attribute_id AND i.store_id IN (0, $store_id))");

        return $select;
    }

}
