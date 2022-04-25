<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class MultishippingResetTotals extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $this->checkoutSession->create()->getQuote();
        /* @var $quote \Epicor\Comm\Model\Quote */        
        /* @var $helper \Epicor\Comm\Helper\Cart\Sendbsv */
        $this->registry->register('dont_send_bsv', true, true);
        $eccData = [
            'ecc_bsv_goods_total' => null, 
            'ecc_bsv_goods_total_inc' => null, 
            'ecc_bsv_carriage_amount' => null, 
            'ecc_bsv_carriage_amount_inc' => null, 
            'ecc_bsv_discount_amount' => null, 
            'ecc_bsv_grand_total' => null, 
            'ecc_bsv_grand_total_inc' => null
        ];

        $quote->addData($eccData);
        foreach($quote->getAllShippingAddresses() as $address) 
        {
            $address->addData($eccData);
        }
        
        $quote->save();
        $this->registry->unregister('dont_send_bsv', true, true);
    }

}