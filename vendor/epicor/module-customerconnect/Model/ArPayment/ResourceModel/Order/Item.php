<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order;

use Epicor\Customerconnect\Model\ArPayment\ResourceModel\EntityAbstract as SalesResource;

/**
 * Flat sales order item resource
 */
class Item extends SalesResource
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'ecc_ar_sales_order_item_resource';

    /**
     * Fields that should be serialized before persistence
     *
     * @var array
     */
    protected $_serializableFields = ['product_options' => [[], []]];

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ecc_ar_sales_order_item', 'item_id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /**@var $object \Epicor\Customerconnect\Model\ArPayment\Order\Item */
        if (!$object->getOrderId() && $object->getOrder()) {
            $object->setOrderId($object->getOrder()->getId());
        }
        if ($object->getParentItem()) {
            $object->setParentItemId($object->getParentItem()->getId());
        }

        return parent::_beforeSave($object);
    }
}
