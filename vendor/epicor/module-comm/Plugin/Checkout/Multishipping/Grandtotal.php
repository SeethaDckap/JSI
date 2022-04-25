<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Checkout\Multishipping;

/**
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Grandtotal
{

    /**
     * Get grandtotal exclude tax
     * Handled this for WSO-6329
     * @return float
     */
    public function aroundGetTotalExclTax(\Magento\Tax\Block\Checkout\Grandtotal $subject)
    {
        if(!isset($subject->_totals['tax'])) {
            return  $subject->getTotal()->getValue();
        } else {
            $excl = $subject->getTotal()->getValue() - $subject->_totals['tax']->getValue();
            $excl = max($excl, 0);
            return $excl;            
        }
    }


}