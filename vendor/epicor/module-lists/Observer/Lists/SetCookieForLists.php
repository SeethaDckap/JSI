<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Observer\Lists;

class SetCookieForLists extends AbstractObserver {

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $quote = $observer->getEvent()->getCart()->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()->setPath('/');
        if (!is_null($this->_cookieManager->getCookie('isListFilterReq'))) {
           $this->_cookieManager->setPublicCookie('isListFilterReq', $this->isListsFilteringReq(), $metadata);
        }
        return $quote;
    }

}
