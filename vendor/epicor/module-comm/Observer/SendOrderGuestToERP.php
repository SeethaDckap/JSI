<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class SendOrderGuestToERP extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    
    /**
     * @var \Epicor\Comm\Helper\BsvAndGor
     */
    protected $bsvAndGorHelper;
    
   /**
    * 
    * @param \Epicor\Comm\Helper\BsvAndGor $bsvAndGorHelper
    */
    public function __construct(
        \Epicor\Comm\Helper\BsvAndGor $bsvAndGorHelper
    ) {
         $this->bsvAndGorHelper = $bsvAndGorHelper;
    }
    
    /**
     * Guest customer/user sending GOR
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        /* @var $order \Epicor\Comm\Model\Order */ 
        
        if($order && $order->getCustomerIsGuest()) {
            $this->bsvAndGorHelper->SendOrderToErp($order);
        }
        return $this;
    }

   
        

}