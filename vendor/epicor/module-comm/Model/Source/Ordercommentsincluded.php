<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Source;

/**
 * Class Log
 */
class Ordercommentsincluded implements \Magento\Framework\Option\ArrayInterface
{
    
    /**
     * @var \Epicor\Comm\Block\Adminhtml\Form\Element\ErpaccountFactory
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context
    ) {
        $this->scopeConfig = $context->getScopeConfig();
    } 
    
    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {   
        $options = [
            'O' => 'Order Text',
            'C' => 'Carriage Text',
            'B' => 'Both',
            'E' => 'ERP will decide'
        ];
        
        if ($this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'p21') {
            unset($options['E']);
        }    
        return $options;
    }
}
