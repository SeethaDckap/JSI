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
class ParamOverriderCartId
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
     * By passing Qty validation
     *
     * @param \Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface $subject
     * @param \Closure $proceed
     * @return array $result
     */


    public function aroundGetOverriddenValue(
        \Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface $subject,
        \Closure $proceed
    ) {
        $this->registry->unregister('QuantityValidatorObserver');
        $this->registry->register('QuantityValidatorObserver', 1);
        $result = $proceed();
        $this->registry->unregister('QuantityValidatorObserver');
        return $result;
    }
}
