<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order;

use Epicor\Customerconnect\Model\ArPayment\ResourceModel\Collection\AbstractCollection;

/**
 * Flat sales order collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'ecc_ar_sales_order_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'order_collection';

    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_coreResourceHelper;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\DB\Helper $coreResourceHelper
     * @param string|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $connection,
            $resource
        );
        $this->_coreResourceHelper = $coreResourceHelper;
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Epicor\Customerconnect\Model\ArPayment\Order::class, \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order::class);
        $this->addFilterToMap(
            'entity_id',
            'main_table.entity_id'
        )->addFilterToMap(
            'customer_id',
            'main_table.customer_id'
        )->addFilterToMap(
            'quote_address_id',
            'main_table.quote_address_id'
        );
    }

    /**
     * Add items count expr to collection select, backward capability with eav structure
     *
     * @return $this
     */
    public function addItemCountExpr()
    {
        if ($this->_fieldsToSelect === null) {
            // If we select all fields from table, we need to add column alias
            $this->getSelect()->columns(['items_count' => 'total_item_count']);
        } else {
            $this->addFieldToSelect('total_item_count', 'items_count');
        }
        return $this;
    }

    /**
     * Minimize usual count select
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        /* @var $countSelect \Magento\Framework\DB\Select */
        $countSelect = parent::getSelectCountSql();
        $countSelect->resetJoinLeft();
        return $countSelect;
    }

    /**
     * Reset left join
     *
     * @param int $limit
     * @param int $offset
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = parent::_getAllIdsSelect($limit, $offset);
        $idsSelect->resetJoinLeft();
        return $idsSelect;
    }

    /**
     * Join table sales_order_address to select for billing and shipping order addresses.
     * Create correlation map
     *
     * @return $this
     */
    protected function _addAddressFields()
    {
        $billingAliasName = 'billing_o_a';
        $shippingAliasName = 'shipping_o_a';
        $joinTable = $this->getTable('ecc_ar_sales_order_address');

        $this->addFilterToMap(
            'billing_firstname',
            $billingAliasName . '.firstname'
        )->addFilterToMap(
            'billing_lastname',
            $billingAliasName . '.lastname'
        )->addFilterToMap(
            'billing_telephone',
            $billingAliasName . '.telephone'
        )->addFilterToMap(
            'billing_postcode',
            $billingAliasName . '.postcode'
        )->addFilterToMap(
            'shipping_firstname',
            $shippingAliasName . '.firstname'
        )->addFilterToMap(
            'shipping_lastname',
            $shippingAliasName . '.lastname'
        )->addFilterToMap(
            'shipping_telephone',
            $shippingAliasName . '.telephone'
        )->addFilterToMap(
            'shipping_postcode',
            $shippingAliasName . '.postcode'
        );

        $this->getSelect()->joinLeft(
            [$billingAliasName => $joinTable],
            "(main_table.entity_id = {$billingAliasName}.parent_id" .
            " AND {$billingAliasName}.address_type = 'billing')",
            [
                $billingAliasName . '.firstname',
                $billingAliasName . '.lastname',
                $billingAliasName . '.telephone',
                $billingAliasName . '.postcode'
            ]
        )->joinLeft(
            [$shippingAliasName => $joinTable],
            "(main_table.entity_id = {$shippingAliasName}.parent_id" .
            " AND {$shippingAliasName}.address_type = 'shipping')",
            [
                $shippingAliasName . '.firstname',
                $shippingAliasName . '.lastname',
                $shippingAliasName . '.telephone',
                $shippingAliasName . '.postcode'
            ]
        );
        $this->_coreResourceHelper->prepareColumnsList($this->getSelect());
        return $this;
    }

    /**
     * Add addresses information to select
     *
     * @return \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Collection\AbstractCollection
     */
    public function addAddressFields()
    {
        return $this->_addAddressFields();
    }

    /**
     * Add field search filter to collection as OR condition
     *
     * @param string $field
     * @param int|string|array|null $condition
     * @return $this
     *
     * @see self::_getConditionSql for $condition
     */
    public function addFieldToSearchFilter($field, $condition = null)
    {
        $field = $this->_getMappedField($field);
        $this->_select->orWhere($this->_getConditionSql($field, $condition));
        return $this;
    }

    /**
     * Specify collection select filter by attribute value
     *
     * @param array $attributes
     * @param array|int|string|null $condition
     * @return $this
     */
    public function addAttributeToSearchFilter($attributes, $condition = null)
    {
        if (is_array($attributes) && !empty($attributes)) {
            $this->_addAddressFields();

            foreach ($attributes as $attribute) {
                $this->addFieldToSearchFilter($this->_attributeToField($attribute['attribute']), $attribute);
            }
        } else {
            $this->addAttributeToFilter($attributes, $condition);
        }

        return $this;
    }

 
}
