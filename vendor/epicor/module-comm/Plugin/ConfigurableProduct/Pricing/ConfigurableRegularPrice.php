<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\ConfigurableProduct\Pricing;

use Magento\Framework\Pricing\Adjustment\CalculatorInterface;

/**
 * Class ConfigurableRegularPrice
 * @package Epicor\Comm\Plugin\ConfigurableProduct\Pricing
 */
class ConfigurableRegularPrice
{
    /**
     * @var CalculatorInterface
     */
    private $calculator;

    /**
     * ConfigurableRegularPrice constructor.
     * @param CalculatorInterface $calculator
     */
    public function __construct(
        CalculatorInterface $calculator
    ) {
        $this->calculator = $calculator;
    }

    /**
     * @param \Magento\ConfigurableProduct\Pricing\Price\ConfigurableRegularPrice $subject
     * @param $result
     * @return \Magento\Framework\Pricing\Amount\AmountInterface|mixed
     */
    public function afterGetMinRegularAmount(
        \Magento\ConfigurableProduct\Pricing\Price\ConfigurableRegularPrice $subject,
        $result
    ){
        $product = $subject->getProduct();
        if ($regularPrice = $product->getRegularPrice()) {
            $result = $this->calculator->getAmount($regularPrice, $product);
        }
        return $result;
    }
}