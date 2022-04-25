<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Punchout\Observer;

class Addproduct implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;


    /**
     * Construction function.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;

    }//end __construct()

    /**
     * execute function.
     *
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote->getIsPunchout() && $this->customerSession->getIsPunchout()) {
            $quote->setIsPunchout(1)->save();
        }
    }

}