<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CheckCartAfterLogin extends AbstractObserver implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /* @var $cart \Epicor\Comm\Model\Cart */
        $cart = $this->checkoutCart->create();

        /* @var $helper \Epicor\Comm\Helper\Configurator */
        $helper = $this->commConfiguratorHelper->create();


        $customerSession = $this->customerSessionFactory->create();
        /* @var $customer \Epicor\Comm\Model\Customer */
        $customer = $customerSession->getCustomer();

        if ($customer->isForcedToMasqurade()) {
            $helper->wipeCart();
        } else {
            if ($helper->removeUnlicensedConfiguratorProducts($cart->getQuote())) {
                $this->checkoutSession->create()->setCartWasUpdated(true);
            }

            /* @var $quote \Epicor\Comm\Model\Quote */
            $quote = $cart->getQuote();

            if ($quote->getArpaymentsQuote()) {
                $quote->setIsActive(0);
                return $this;
            }

            if ($quote->getIsPunchout() || $customerSession->getIsPunchout()) {
                $quote->setIsActive(0)->save();
                return $this;
            }
            $this->quoteProcess($quote);
        }
    }

    /**
     * Quote Process
     *
     * @param Observer $quote
     * @return $this|void
     */
    private function quoteProcess($quote)
    {

        if ($quote->getItemsCount() > 0 || $quote->getEccQuoteId()) {
            if ($quote->getEccQuoteId()) {
                $quote->setEccQuoteId(null);
                $quote->setEccErpQuoteId(null);
                $quote->removeAllItems();
            }

            if (!$this->registry->registry('bsv_sent')) {
                $this->customerSessionFactory->create()->setBsvTriggerTotals(array());
                $this->registry->unregister('after_login_msq_init');
                $this->registry->register('after_login_msq_init', 1);
                $quote->setTotalsCollectedFlag(false);
                $quote->collectTotals();
            }
        }

    }

}
