<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Quote;

class SendBsvPlugin {

    /**
     * @var \Epicor\Comm\Helper\Cart\SendbsvFactory
     */
    protected $sendBsvHelperFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    public function __construct(
        \Epicor\Comm\Helper\Cart\SendbsvFactory $sendBsvHelperFactory,
        \Magento\Framework\Registry $registry
    )
    {
        $this->sendBsvHelperFactory = $sendBsvHelperFactory;
        $this->registry = $registry;
    }
    
    /**
     * Send BSV after quote collect totals is run
     * 
     * @param \Magento\Quote\Model\Quote $subject
     * @param \Magento\Quote\Model\Quote $return
     * @return type
     */
    public function afterCollectTotals(\Magento\Quote\Model\Quote $subject, $return)
    {
        //don't send BSV on clearCheckoutOfNonErpItems() after GOR
        if(!$this->registry->registry("non_erp_parts_deleted")) {
            /* @var $helper \Epicor\Comm\Helper\Cart\Sendbsv */
            $helper = $this->sendBsvHelperFactory->create();
            $helper->sendCartBsv($subject);
        }else{
            $this->registry->unregister("non_erp_parts_deleted");
        }

        return $return;
    }
    
    /**
     * Remove ECC BSV values before item is removed
     * 
     * @param \Magento\Quote\Model\Quote $subject
     * @param integer $itemId - no need to use this
     */
    public function beforeRemoveItem(\Magento\Quote\Model\Quote $subject, $itemId) 
    {
        $eccData = [
            'ecc_bsv_goods_total' => null, 
            'ecc_bsv_goods_total_inc' => null, 
            'ecc_bsv_carriage_amount' => null, 
            'ecc_bsv_carriage_amount_inc' => null, 
            'ecc_bsv_grand_total' => null, 
            'ecc_bsv_grand_total_inc' => null
        ];

        $subject->addData($eccData);
        if ($subject->getShippingAddress()) {
            $subject->getShippingAddress()->addData($eccData);
        }
    }
    
}
