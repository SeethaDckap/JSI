<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order;

use Epicor\Customerconnect\Model\ArPayment\ResourceModel\EntityAbstract as SalesResource;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

/**
 * Flat sales order address resource
 */
class Address extends SalesResource
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'ecc_ar_sales_order_address_resource';

    /**
     * @var \Epicor\Customerconnect\Model\ArPayment\Order\Address\Validator
     */
    protected $_validator;

    /**
     * @var \Epicor\Customerconnect\Model\ArPayment\ResourceModel\GridPool
     */
    protected $gridPool;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Attribute $attribute
     * @param Snapshot $entitySnapshot
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param \Epicor\Customerconnect\Model\ArPayment\Order\Address\Validator $validator
     * @param \Epicor\Customerconnect\Model\ArPayment\ResourceModel\GridPool $gridPool
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Attribute $attribute,
        \Epicor\Customerconnect\Model\ArPayment\Order\Address\Validator $validator,
        \Epicor\Customerconnect\Model\ArPayment\ResourceModel\GridPool $gridPool,
        $connectionName = null
    ) {
        $this->_validator = $validator;
        $this->gridPool = $gridPool;
        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $attribute,
            $connectionName
        );
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ecc_ar_sales_order_address', 'entity_id');
    }

    /**
     * Return configuration for all attributes
     *
     * @return array
     */
    public function getAllAttributes()
    {
        $attributes = [
            'city' => __('City'),
            'company' => __('Company'),
            'country_id' => __('Country'),
            'email' => __('Email'),
            'firstname' => __('First Name'),
            'lastname' => __('Last Name'),
            'region_id' => __('State/Province'),
            'street' => __('Street Address'),
            'telephone' => __('Phone Number'),
            'postcode' => __('Zip/Postal Code'),
        ];
        asort($attributes);
        return $attributes;
    }

    /**
     * Performs validation before save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);
        if (!$object->getParentId() && $object->getOrder()) {
            $object->setParentId($object->getOrder()->getId());
        }
        // Init customer address id if customer address is assigned
        $customerData = $object->getCustomerAddressData();
        if ($customerData) {
            $object->setCustomerAddressId($customerData->getId());
        }
        $warnings = $this->_validator->validate($object);
        if (!empty($warnings)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("We can't save the address:\n%1", implode("\n", $warnings))
            );
        }
        return $this;
    }
}
