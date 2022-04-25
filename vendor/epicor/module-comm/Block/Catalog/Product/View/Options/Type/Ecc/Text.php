<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product\View\Options\Type\Ecc;


class Text extends \Magento\Catalog\Block\Product\View\Options\Type\Text
{

    protected $_template = 'epicor_comm/catalog/product/view/options/type/ecc/text.phtml';
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $pricingHelper,
            $catalogData,
            $data
        );
    }


    public function getValidationClass($option)
    {
        $class = '';

        switch ($option->getEccValidationCode()) {
            case 'CSNS':
                $class = 'validate-csns';
                break;
            case 'email':
                $class = 'validate-email';
                break;
            case 'alphanumeric':
                $class = 'validate-alphanum';
                break;
            case 'numeric':
                $class = 'validate-number';
                break;
        }

        return $class;
    }

}
