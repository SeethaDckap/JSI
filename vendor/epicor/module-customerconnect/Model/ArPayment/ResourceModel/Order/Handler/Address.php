<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\Handler;

use Epicor\Customerconnect\Model\ArPayment\Order;
use Epicor\Customerconnect\Model\ArPayment\ResourceModel\Attribute;

/**
 * Class Address
 */
class Address
{
    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * @param Attribute $attribute
     */
    public function __construct(
        Attribute $attribute
    ) {
        $this->attribute = $attribute;
    }

    /**
     * Remove empty addresses from order
     *
     * @param Order $order
     * @return $this
     */
    public function removeEmptyAddresses(Order $order)
    {
        if ($order->hasBillingAddressId() && $order->getBillingAddressId() === null) {
            $order->unsBillingAddressId();
        }

        if ($order->hasShippingAddressId() && $order->getShippingAddressId() === null) {
            $order->unsShippingAddressId();
        }
        return $this;
    }

    /**
     * Process addresses saving
     *
     * @param Order $order
     * @return $this
     * @throws \Exception
     */
    public function process(Order $order)
    {
        if (null !== $order->getAddresses()) {
            /** @var \Epicor\Customerconnect\Model\ArPayment\Order\Address $address */
            foreach ($order->getAddresses() as $address) {
                $address->setParentId($order->getId());
                $address->setOrder($order);
                $address->save();
            }
            $billingAddress = $order->getBillingAddress();
            $attributesForSave = [];
            if ($billingAddress && $order->getBillingAddressId() != $billingAddress->getId()) {
                $order->setBillingAddressId($billingAddress->getId());
                $attributesForSave[] = 'billing_address_id';
            }
            $shippingAddress = $order->getShippingAddress();
            if ($shippingAddress && $order->getShippigAddressId() != $shippingAddress->getId()) {
                $order->setShippingAddressId($shippingAddress->getId());
                $attributesForSave[] = 'shipping_address_id';
            }
            if (!empty($attributesForSave)) {
                $this->attribute->saveAttribute($order, $attributesForSave);
            }
        }
        return $this;
    }
}
