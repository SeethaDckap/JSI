<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Checkout;

use Magento\Framework\Exception\InputException;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionFactory;
/**
 * One page checkout processing model
 */
class PaymentMethodManagement
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     *
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    )
    {
        $this->registry = $registry;
    }

    /**
     * By passing Qty validation.
     *
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $subject
     * @param \Closure $proceed
     * @param int $cartId
     * @return array $result
     */

    public function aroundGetList(
        \Magento\Quote\Api\PaymentMethodManagementInterface $subject,
        \Closure $proceed,
        $cartId
    ) {
        $this->registry->unregister('QuantityValidatorObserver');
        $this->registry->register('QuantityValidatorObserver', 1);
        $result = $proceed($cartId);
        $this->registry->unregister('QuantityValidatorObserver');
        return $result;
    }
}
