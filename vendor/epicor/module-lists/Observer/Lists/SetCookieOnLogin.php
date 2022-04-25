<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Lists;

class SetCookieOnLogin extends AbstractObserver
{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()->setPath('/');
        $listsEnabled = $this->isListsFilteringReq();
        if (is_null($this->_cookieManager->getCookie('isListFilterReq')) && $listsEnabled) {
            $this->_cookieManager->setPublicCookie('isListFilterReq', 0, $metadata);
        } else if($listsEnabled) {
            $this->_cookieManager->setPublicCookie('isListFilterReq', 0, $metadata);
        }
    }

}