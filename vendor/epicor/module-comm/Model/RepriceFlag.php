<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model;

class RepriceFlag
{
    /**
     * @param $discount
     * @param $quantity
     * @param $preventReprice
     * @return string
     */
    public static function getItemRepricingFlag ($discount, $quantity, $preventReprice)
    {
        $reprice = 'Y';
        if ($preventReprice === false || $preventReprice === 'N') {
            return ($discount * $quantity) < 0 ? 'Y' : 'N';
        }

        return $reprice;
    }
}
