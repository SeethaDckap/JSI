<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

// @codingStandardsIgnoreFile

namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

/**
 * Quote resource model
 */
class Quote extends AbstractDb
{

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Snapshot $entitySnapshot,
     * @param RelationComposite $entityRelationComposite,
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        $connectionName = null
    ) {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $connectionName);
    }

    /**
     * Initialize table nad PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ecc_ar_quote', 'entity_id');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $storeIds = $object->getSharedStoreIds();
        if ($storeIds) {
            if ($storeIds != ['*']) {
                $select->where('store_id IN (?)', $storeIds);
            }
        } else {
            /**
             * For empty result
             */
            $select->where('store_id < ?', 0);
        }

        return $select;
    }

    /**
     * Load quote data by customer identifier
     *
     * @param \Epicor\Customerconnect\Model\ArPayment\Quote $quote
     * @param int $customerId
     * @return $this
     */
    public function loadByCustomerId($quote, $customerId)
    {
        $connection = $this->getConnection();
        $select = $this->_getLoadSelect(
            'customer_id',
            $customerId,
            $quote
        )->where(
            'is_active = ?',
            1
        )->order(
            'updated_at ' . \Magento\Framework\DB\Select::SQL_DESC
        )->limit(
            1
        );

        $data = $connection->fetchRow($select);

        if ($data) {
            $quote->setData($data);
        }

        $this->_afterLoad($quote);

        return $this;
    }

    /**
     * Load only active quote
     *
     * @param \Epicor\Customerconnect\Model\ArPayment\Quote $quote
     * @param int $quoteId
     * @return $this
     */
    public function loadActive($quote, $quoteId)
    {
        $connection = $this->getConnection();
        $select = $this->_getLoadSelect('entity_id', $quoteId, $quote)->where('is_active = ?', 1);

        $data = $connection->fetchRow($select);
        if ($data) {
            $quote->setData($data);
        }

        $this->_afterLoad($quote);

        return $this;
    }

    /**
     * Load quote data by identifier without store
     *
     * @param \Epicor\Customerconnect\Model\ArPayment\Quote $quote
     * @param int $quoteId
     * @return $this
     */
    public function loadByIdWithoutStore($quote, $quoteId)
    {
        $connection = $this->getConnection();
        if ($connection) {
            $select = parent::_getLoadSelect('entity_id', $quoteId, $quote);

            $data = $connection->fetchRow($select);

            if ($data) {
                $quote->setData($data);
            }
        }

        $this->_afterLoad($quote);
        return $this;
    }


    /**
     * Mark quotes - that depend on catalog price rules - to be recollected on demand
     *
     * @return $this
     */
    public function markQuotesRecollectOnCatalogRules()
    {
        $tableQuote = $this->getTable('ecc_ar_quote');
        $subSelect = $this->getConnection()->select()->from(
            ['t2' => $this->getTable('ecc_ar_quote_item')],
            ['entity_id' => 'quote_id']
        )->from(
            ['t3' => $this->getTable('catalogrule_product_price')],
            []
        )->where(
            't2.product_id = t3.product_id'
        )->group(
            'quote_id'
        );

        $select = $this->getConnection()->select()->join(
            ['t2' => $subSelect],
            't1.entity_id = t2.entity_id',
            ['trigger_recollect' => new \Zend_Db_Expr('1')]
        );

        $updateQuery = $select->crossUpdateFromSelect(['t1' => $tableQuote]);

        $this->getConnection()->query($updateQuery);

        return $this;
    }

    /**
     * Subtract product from all quotes quantities
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function subtractProductFromQuotes($product)
    {
        $productId = (int)$product->getId();
        if (!$productId) {
            return $this;
        }
        $connection = $this->getConnection();
        $subSelect = $connection->select();
        $conditionCheck = $connection->quoteIdentifier('q.items_count') . " > 0";
        $conditionTrue = $connection->quoteIdentifier('q.items_count') . ' - 1';
        $ifSql = "IF (" . $conditionCheck . "," . $conditionTrue . ", 0)";

        $subSelect->from(
            false,
            [
                'items_qty' => new \Zend_Db_Expr(
                    $connection->quoteIdentifier('q.items_qty') . ' - ' . $connection->quoteIdentifier('qi.qty')
                ),
                'items_count' => new \Zend_Db_Expr($ifSql)
            ]
        )->join(
            ['qi' => $this->getTable('ecc_ar_quote_item')],
            implode(
                ' AND ',
                [
                    'q.entity_id = qi.quote_id',
                    'qi.parent_item_id IS NULL',
                    $connection->quoteInto('qi.product_id = ?', $productId)
                ]
            ),
            []
        );

        $updateQuery = $connection->updateFromSelect($subSelect, ['q' => $this->getTable('ecc_ar_quote')]);

        $connection->query($updateQuery);

        return $this;
    }

    /**
     * Subtract product from all quotes quantities
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @deprecated 101.0.1
     * @see \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Quote::subtractProductFromQuotes
     *
     * @return $this
     */
    public function substractProductFromQuotes($product)
    {
        return $this->subtractProductFromQuotes($product);
    }

    /**
     * Mark recollect contain product(s) quotes
     *
     * @param array|int|\Zend_Db_Expr $productIds
     * @return $this
     */
    public function markQuotesRecollect($productIds)
    {
        $tableQuote = $this->getTable('ecc_ar_quote');
        $tableItem = $this->getTable('ecc_ar_quote_item');
        $subSelect = $this->getConnection()->select()->from(
            $tableItem,
            ['entity_id' => 'quote_id']
        )->where(
            'product_id IN ( ? )',
            $productIds
        )->group(
            'quote_id'
        );

        $select = $this->getConnection()->select()->join(
            ['t2' => $subSelect],
            't1.entity_id = t2.entity_id',
            ['trigger_recollect' => new \Zend_Db_Expr('1')]
        );
        $updateQuery = $select->crossUpdateFromSelect(['t1' => $tableQuote]);
        $this->getConnection()->query($updateQuery);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->isPreventSaving()) {
            return parent::save($object);
        }
    }
}
