<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\SalesRep\Observer\Cart;

class CheckForSalesRepPrices extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * CheckoutSession.
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * Checkout page Send Bsv.
     * @param \Epicor\SalesRep\Helper\Data $salesRepHelper
     * @param \Mag\Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @throws \Magento\Framework\Exception\SessionException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */

    public function __construct(
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        parent::__construct($salesRepHelper, $customerSession, $request);
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @var \Magento\Framework\App\Request\Http
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $salesRepHelper = $this->salesRepHelper;
        /* @var $salesRepHelper Epicor_SalesRep_Helper_Data */

        if (!$salesRepHelper->isEnabled()) {
            return;
        }

        $cart = $observer->getEvent()->getCart();
        /* @var $cart Mage_Checkout_Model_Cart */
        $recalculate = false;
        $postedCartItems = $this->request->getParam('cart', array());
        foreach ($postedCartItems as $itemId => $itemData) {

            if (array_key_exists('calculation_price', $itemData)) {
                $item = $cart->getQuote()->getItemById($itemId);
                /* @var $cartItem Mage_Sales_Model_Quote_Item */
                if (!$item) {
                    continue;
                }
                $recalculate = true;
                $item->setEccSalesrepPrice($itemData['calculation_price']);
                $item->setEccSalesrepDiscount($itemData['discount_percent']);
                $item->save();
            }
        }
        if ($recalculate) {
            $this->checkoutSession->unsetData('bsv_sent_for_cart_page');
        }
    }

}
