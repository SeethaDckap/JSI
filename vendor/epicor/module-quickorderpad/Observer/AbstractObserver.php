<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Observer;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $checkoutCart;

    protected $checkoutSession;

    protected $storeManager;

    public function __construct(
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
    }

    protected function _isUrlInternal($url)
    {
        if (strpos($url, 'http') !== false) {
            if ((strpos($url, $this->storeManager->getStore()->getBaseUrl()) === 0) || (strpos($url, $this->storeManager->getStore()->getBaseUrl(\Magento\Store\Model\Store::URL_TYPE_LINK, true)) === 0)
            ) {
                return true;
            }
        }
        return false;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }

}

