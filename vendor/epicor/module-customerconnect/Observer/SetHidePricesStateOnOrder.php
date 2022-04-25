<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Epicor\Comm\Helper\Data as CommHelper;
use Magento\Sales\Model\Order;

class SetHidePricesStateOnOrder implements ObserverInterface
{
    private $commHelper;

    public function __construct(CommHelper $commHelper )
    {

        $this->commHelper = $commHelper;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getData('order');
        if ($order instanceof Order && $this->isCustomerLoggedIn()) {
            $order->setData('hide_prices', $this->getHidePriceState());
        }
    }

    private function isCustomerLoggedIn(): bool
    {
        $customerSession = $this->commHelper->customerSessionFactory();
        return $customerSession->isLoggedIn();
    }

    private function getHidePriceState()
    {
        if($this->commHelper->getEccHidePrice() && in_array($this->commHelper->getEccHidePrice(), [1,3])){
            return 1;
        }

        return 0;
    }
}