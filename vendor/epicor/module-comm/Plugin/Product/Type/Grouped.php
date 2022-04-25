<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Product\Type;


class Grouped
{

    public function afterGetAssociatedProductCollection(
        \Magento\GroupedProduct\Model\Product\Type\Grouped $subject,
        $result
    ) {
        $result->addAttributeToSelect(
            ['ecc_pack_size','ecc_stk_type','ecc_default_uom', 'ecc_decimal_places', 'is_ecc_discontinued', 'is_ecc_non_stock']
        );
         
        return $result;
    }
    
    
    
}