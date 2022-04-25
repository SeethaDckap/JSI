<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Cre\Block\Adminhtml\Order\View\Tab;


class Info extends \Magento\Backend\Block\Template
{
    
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    private $tokenRequestData;
    
    
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Epicor\Cre\Helper\CreData $CreData, 
                                \Magento\Framework\Registry $registry,
                                array $data = [])
    {
        $this->registry         = $registry;
        $this->tokenRequestData = $CreData;
        parent::__construct($context, $data);
    }
    
    /**
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function getPayment()
    {
        $order = $this->registry->registry('current_order');
        return $order->getPayment();
    }

    public function getCreLogo()
    {
        return $this->tokenRequestData->getCreLogo();
    } 


    public function getCardType()
    {
        return '<img src="' . $this->tokenRequestData->getCardTypeImage($this->getPayment()->getCcType()) . '" alt="' . $this->getPayment()->getCcType() . '"/>';
    }       
    
    
    /**
     * @return string
     */
    protected function _toHtml()
    {
        return ($this->getPayment()->getMethod() === "cre") ? parent::_toHtml() : '';
    }
    
}