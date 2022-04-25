<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer;

class CuadMessageLog extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
     /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
     public function __construct(   
        \Magento\Framework\Registry $registry      
    ) {
        $this->registry = $registry;  
    }
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $message = $observer->getEvent()->getMessage();
        if (!$this->registry->registry('cuad_invoice_address_exists')) {


            //this removes the previously set 'Success'
            $message->unsetStatusDescription();

            $message->setStatusDescription($message->getErrorDescription($message::STATUS_INVOICE_ADDRESS_NOT_SUPPLIED_ERROR, 'CUAD'));

            $message->setStatusCode($message::STATUS_INVOICE_ADDRESS_NOT_SUPPLIED_ERROR);
            $message->setStatus(\Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_ERROR);
        } else {
            $message->setStatus(\Epicor\Comm\Model\Message\Log::MESSAGE_STATUS_SUCCESS);
        }
        return $message;
    }

}
