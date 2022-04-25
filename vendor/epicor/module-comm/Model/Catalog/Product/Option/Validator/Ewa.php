<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Catalog\Product\Option\Validator;

use Magento\Catalog\Model\Product\Option;

class Ewa extends \Magento\Catalog\Model\Product\Option\Validator\DefaultValidator
{   

    /**
     * Validate option type fields
     *
     * @param Option $option
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function validateOptionValue(Option $option)
    {
        return true;
    }

}
