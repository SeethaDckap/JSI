<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Minicart;

class ProductNamePlugin {

    public function aroundGetItemData($subject, $proceed, $item) {
        $result = $proceed($item);
        if (strpos($result['product_name'], '&quot;') !== false) { 
            $result['product_name'] = str_replace('&quot;','"', $result['product_name']);
        }   
        return $result;
    }

}
