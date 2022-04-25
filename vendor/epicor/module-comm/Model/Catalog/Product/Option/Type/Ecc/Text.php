<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Catalog\Product\Option\Type\Ecc;

class Text extends \Magento\Catalog\Model\Product\Option\Type\Text
{

    public function __construct(
    \Magento\Checkout\Model\Session $checkoutSession, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\Escaper $escaper, \Magento\Framework\Stdlib\StringUtils $string, array $data = []
    )
    {
        parent::__construct(
            $checkoutSession, $scopeConfig, $escaper, $string, $data
        );
    }

}
