<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Catalog\Helper\Product;


class Configuration
{

    /**
     * @var \Magento\Eav\Model\Attribute\Data\Text
     */
    protected $ewaHelper;


    public function __construct(
        \Epicor\Comm\Helper\Configurator $ewaHelper
    ) {
        $this->ewaHelper = $ewaHelper;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function aroundGetCustomOptions(
        \Magento\Catalog\Helper\Product\Configuration $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
    ) {
        
        $options = $proceed($item);
        if($item->getProduct()->getCustomAttribute('ecc_configurator') && $item->getProduct()->getCustomAttribute('ecc_configurator')->getValue()){            
            $options = $this->ewaHelper->getEwaOptions($options);
        }
        return $options;
    }

   
    
}