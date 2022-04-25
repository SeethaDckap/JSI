<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Catalog\Layer\Filter;


/**
 * Catalog Layer Attribute Filter Resource Model
 *
 * Overidden to allow text attributes to work with layered navigation
 * 
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Attribute extends \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute
{

    /**
     * Apply attribute filter to product collection
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Attribute $filter
     * @param int $value
     * @return \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute
     */
    public function applyFilterToCollection(\Magento\Catalog\Model\Layer\Filter\FilterInterface $filter, $value)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $attribute = $filter->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = $attribute->getAttributeCode() . '_idx';

        if ($attribute->getFrontend()->getInputType() == 'text' || $attribute->getFrontend()->getInputType() == 'boolean') {
            $table = 'catalog_product_entity_' . $attribute->getBackendType();
            $store = ($attribute->getIsGlobal()) ? 0 : $filter->getStoreId();
        } else {
            $table = $this->getMainTable();
            $store = $filter->getStoreId();
        }

        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $store),
            $connection->quoteInto("{$tableAlias}.value = ?", $value)
        );

        $collection->getSelect()->join(
            array($tableAlias => $table), implode(' AND ', $conditions), array()
        );

        return $this;
    }

    /**
     * Retrieve array with products counts per attribute option
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Attribute $filter
     * @return array
     */
    public function getCount(\Magento\Catalog\Model\Layer\Filter\FilterInterface $filter)
    {
        // clone select from collection with filters
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(\Zend_Db_Select::COLUMNS);
        $select->reset(\Zend_Db_Select::ORDER);
        $select->reset(\Zend_Db_Select::LIMIT_COUNT);
        $select->reset(\Zend_Db_Select::LIMIT_OFFSET);

        $connection = $this->_getReadAdapter();
        $attribute = $filter->getAttributeModel();

        if ($attribute->getFrontend()->getInputType() == 'text' || $attribute->getFrontend()->getInputType() == 'boolean') {
            $table = 'catalog_product_entity_' . $attribute->getBackendType();
            $store = ($attribute->getIsGlobal()) ? 0 : $filter->getStoreId();
        } else {
            $table = $this->getMainTable();
            $store = $filter->getStoreId();
        }

        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $store),
        );

        $select
            ->join(
                array($tableAlias => $table), join(' AND ', $conditions), array('value', 'count' => new \Zend_Db_Expr("COUNT({$tableAlias}.entity_id)")))
            ->group("{$tableAlias}.value");

        return $connection->fetchPairs($select);
    }

}
