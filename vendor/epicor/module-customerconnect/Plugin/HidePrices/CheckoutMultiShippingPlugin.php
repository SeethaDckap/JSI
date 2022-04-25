<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Plugin\HidePrices;

use Epicor\Customerconnect\Model\EccHidePrices\HidePrice;

class CheckoutMultiShippingPlugin
{
    private $hidePrice;

    /**
     * CheckoutMultiShippingPlugin constructor.
     * @param HidePrice $hidePrice
     */
    public function __construct(
        HidePrice $hidePrice
    ) {

        $this->hidePrice = $hidePrice;
    }

    public function afterGetShippingPrice($subject, $result)
    {
        if (!$this->hidePrice->isHidePricesCheckoutYesActive()) {
            return $result;
        }
    }
}
