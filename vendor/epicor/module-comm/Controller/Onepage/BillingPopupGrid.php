<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Onepage;

class BillingPopupGrid extends \Magento\Framework\App\Action\Action
{
    protected $_gridFactory;
    
    protected $_session;

    
    public function __construct(\Magento\Framework\App\Action\Context $context, 
                                \Magento\Customer\Model\Session $customerSession)
    {
        $this->_session         = $customerSession;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $block = $this->_view->getLayout()->createBlock('Epicor\Comm\Block\Customer\Account\Billingaddress\Listing');
        $this->getResponse()->setBody($block->toHtml());
    }
    
}    