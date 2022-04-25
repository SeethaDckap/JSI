<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Observer;

class ReturnToQuickorderpad extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Magento\Framework\Escaper
     */
    protected $escaper;

    public function __construct(
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Escaper $escaper
    )
    {
        $this->escaper  = $escaper;
        parent::__construct($checkoutCart, $checkoutSession, $storeManager);
    }


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getEvent()->getResponse();
        $request = $observer->getEvent()->getRequest();
        $item = $observer->getEvent()->getItem();
        $returnUrl = $request->getParam('return_url');
        if ($returnUrl) {

            if (!$this->_isUrlInternal($returnUrl)) {
                throw new \Magento\Framework\Exception\LocalizedException('External urls redirect to "' . $returnUrl . '" denied!');
            }
            if (!$this->checkoutCart->getQuote()->getHasError()) {
                //M1 > M2 Translation Begin (Rule 55)
                //M1 > M2 Translation Begin (Rule 20)
                //$message = __('%s was updated in your shopping cart.', Mage::helper('core')->escapeHtml($item->getProduct()->getName()));
                $message = __('%1 was updated in your shopping cart.', $this->escaper->escapeHtml($item->getProduct()->getName()));
                //M1 > M2 Translation End
                //M1 > M2 Translation End
                $this->checkoutSession->addSuccess($message);
            }
            $response->setRedirect($returnUrl);
            $response->sendHeadersAndExit();
        }
    }

}