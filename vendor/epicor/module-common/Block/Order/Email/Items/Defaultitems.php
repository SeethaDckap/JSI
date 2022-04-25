<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Order\Email\Items;

/**
 * Order history block override
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
    
class Defaultitems extends \Magento\Sales\Block\Order\Email\Items\DefaultItems
{
   protected $commHelper; 
 /**
     * Constructor
     *
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, 
                                \Epicor\Comm\Helper\Data $commHelper,
                                array $data = [])
    {
        
        $this->commHelper = $commHelper;
        parent::__construct($context, $data);
    }
    public function setTemplate($template) {
        
        // set correct template for the current template supplied
        preg_match('/items\/(.*?)\/default.phtml/s', $template, $matches);
        
        if(isset($matches[1])){            
            return parent::setTemplate("Epicor_Common::epicor_common/email/order/items/{$matches[1]}/default.phtml");
        }
        return $template;
    }
    public function commHelperGetUom($sku){
        return $this->commHelper->getUom($sku);
    }
}
