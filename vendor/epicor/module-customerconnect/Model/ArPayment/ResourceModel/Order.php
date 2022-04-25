<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\Math\Random;
use Epicor\Customerconnect\Model\ArPayment\ResourceModel\EntityAbstract as SalesResource;
use Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\Handler\State as StateHandler;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;

/**
 * Flat sales order resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Order extends SalesResource
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'ecc_ar_sales_order_resource';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'resource';

    /**
     * @var StateHandler
     */
    protected $stateHandler;

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ecc_ar_sales_order', 'entity_id');
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Attribute $attribute
     * @param Snapshot $entitySnapshot
     * @param RelationComposite $entityRelationComposite
     * @param StateHandler $stateHandler
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        Attribute $attribute,
        StateHandler $stateHandler,
        $connectionName = null
    ) {
        $this->stateHandler = $stateHandler;
        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $attribute,
            $connectionName
        );
    }

    /**
     * Count existent products of order items by specified product types
     *
     * @param int $orderId
     * @param array $productTypeIds
     * @param bool $isProductTypeIn
     * @return array
     */
    public function aggregateProductsByTypes($orderId, $productTypeIds = [], $isProductTypeIn = false)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['o' => $this->getTable('ecc_ar_sales_order_item')],
                ['o.product_type', new \Zend_Db_Expr('COUNT(*)')]
            )
            ->where('o.order_id=?', $orderId)
            ->where('o.product_id IS NOT NULL')
            ->group('o.product_type');
        if ($productTypeIds) {
            $select->where(
                sprintf(
                    '(o.product_type %s (?))',
                    $isProductTypeIn ? 'IN' : 'NOT IN'
                ),
                $productTypeIds
            );
        }
        return $connection->fetchPairs($select);
    }

    /**
     * Process items dependency for new order, returns qty of affected items;
     *
     * @param \Epicor\Customerconnect\Model\ArPayment\Order $object
     * @return int
     */
    protected function calculateItems(\Epicor\Customerconnect\Model\ArPayment\Order $object)
    {
        $itemsCount = 0;
        if (!$object->getId()) {
            foreach ($object->getAllItems() as $item) {
                /** @var  \Epicor\Customerconnect\Model\ArPayment\Order\Item $item */
                $parent = $item->getQuoteParentItemId();
                if ($parent && !$item->getParentItem()) {
                    $item->setParentItem($object->getItemByQuoteItemId($parent));
                }
                $childItems = $item->getChildrenItems();
                if (empty($childItems)) {
                    $itemsCount++;
                }
            }
        }
        return $itemsCount;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getId()) {
            /** @var \Magento\Store\Model\Store $store */
            $store = $object->getStore();
            $name = [
                $store->getWebsite()->getName(),
                $store->getGroup()->getName(),
                $store->getName(),
            ];
            $object->setStoreName(implode(PHP_EOL, $name));
            $object->setTotalItemCount($this->calculateItems($object));
        }
        $object->setData(
            'protect_code',
            substr(
                hash('sha256', uniqid(Random::getRandomNumber(), true) . ':' . microtime(true)),
                5,
                32
            )
        );
        $isNewCustomer = !$object->getCustomerId() || $object->getCustomerId() === true;
        if ($isNewCustomer && $object->getCustomer()) {
            $object->setCustomerId($object->getCustomer()->getId());
        }
        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Epicor\Customerconnect\Model\ArPayment\Order $object */
        $this->stateHandler->check($object);
        return parent::save($object);
    }
}
