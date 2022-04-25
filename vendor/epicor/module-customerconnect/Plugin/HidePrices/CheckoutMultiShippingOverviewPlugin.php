<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Plugin\HidePrices;


use Epicor\Customerconnect\Model\EccHidePrices\HidePrice;

class CheckoutMultiShippingOverviewPlugin
{
    private $hidePrice;

    public function __construct(
        HidePrice $hidePrice
    ) {
        $this->hidePrice = $hidePrice;
    }

    public function afterGetShippingPriceInclTax($subject, $result)
    {
        if (!$this->hidePrice->isHidePricesCheckoutYesActive()) {
            return $result;
        }
    }

    public function afterGetShippingPriceExclTax($subject, $result)
    {
        if (!$this->hidePrice->isHidePricesCheckoutYesActive()) {
            return $result;
        }
    }
}
