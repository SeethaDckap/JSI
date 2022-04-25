<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Catalog\Product\Compare;

use Magento\Customer\Model\Session as CustomerSession;

class ListCompare
{
    /**
     * if Attribute has empty value show as N/A
     *
     * @return mixed
     */
    public function afterGetProductAttributeValue(
        \Magento\Catalog\Block\Product\Compare\ListCompare $subject,
        $proceed,
        $product,
        $attribute
    )
    {
        if (!$product->hasData($attribute->getAttributeCode())) {
            return __('N/A');
        }

        if ($attribute->getSourceModel() || in_array(
                $attribute->getFrontendInput(),
                ['select', 'boolean', 'multiselect']
            )
        ) {
            $value = $attribute->getFrontend()->getValue($product);
        } else {
            $value = $product->getData($attribute->getAttributeCode());
        }
        return (string)$value == '' ? __('N/A') : $value;
    }
}
