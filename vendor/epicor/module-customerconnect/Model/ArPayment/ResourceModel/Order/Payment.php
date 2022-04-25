<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order;

use Magento\Framework\App\ResourceConnection;
use Epicor\Customerconnect\Model\ArPayment\ResourceModel\EntityAbstract as SalesResource;

/**
 * Flat sales order payment resource
 */
class Payment extends SalesResource
{
    /**
     * Serializeable field: additional_information
     *
     * @var array
     */
    protected $_serializableFields = ['additional_information' => [null, []]];

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'ecc_ar_sales_order_payment_resource';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ecc_ar_sales_order_payment', 'entity_id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /**@var $object \Epicor\Customerconnect\Model\ArPayment\Order\Payment */
        parent::_beforeSave($object);
        if (!$object->getParentId() && $object->getOrder()) {
            $object->setParentId($object->getOrder()->getId());
        }
        return $this;
    }
}
