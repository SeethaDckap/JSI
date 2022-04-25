<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Catalog\Product;

/**
 * Stock level display block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Stockdisplay extends \Magento\Framework\View\Element\Template
{
 
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }
    
    
    /**
     * Gets the current product context
     * 
     * @return \Epicor\Comm\Model\Product $_product
     */
    public function getCurrentProduct() {
        
        return $this->registry->registry('current_product');
    }
    
}
