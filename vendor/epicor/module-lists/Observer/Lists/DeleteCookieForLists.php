<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Observer\Lists;

class DeleteCookieForLists extends AbstractObserver {

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $quote = $observer->getEvent()->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()->setPath('/');
        if (!is_null($this->_cookieManager->getCookie('isListFilterReq'))) {
            $this->_cookieManager->deleteCookie('isListFilterReq',$metadata);
        }
        return $quote;
    }

}
