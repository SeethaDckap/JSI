<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Plugin\Checkout;

class AttributeMergerPlugin
{
    public function afterMerge(\Magento\Checkout\Block\Checkout\AttributeMerger $subject, $result)
    {
        if (array_key_exists('firstname', $result)) {
            $result['firstname']['additionalClasses'] = 'firstname';
        }
        if (array_key_exists('lastname', $result)) {
            $result['lastname']['additionalClasses'] = 'lastname';
        }
        return $result;
    }
}
