<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer\Arpayments;

class ArpaymentsLayoutLoadBefore extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    protected $_request;
    
    public function __construct(\Magento\Framework\Registry $registry, \Magento\Framework\App\Request\Http $request)
    {
        $this->_registry = $registry;
        $this->_request  = $request;
    }
    
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $handle = $this->_request->getFullActionName();
        if ($handle == "customerconnect_arpayments_archeckout") {
            $layout = $observer->getLayout();
            $layout->getUpdate()->addHandle('checkout_index_index');
            $layout->getUpdate()->addHandle('customerconnect_arpayments_arstyle');
            return $this;
        }
    }
}