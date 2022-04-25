<?php

/**
 * UDExample
 * Copyright (C) 2017 Test
 * 
 * This file is part of Epicor/UDExample.
 * 
 * Epicor/UDExample is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Epicor\UDExample\Observer\Crqu;

class RequestConvert2xmlBefore implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Magento\Customer\Model\Session $customerSession
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->customerSession = $customerSession;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
    \Magento\Framework\Event\Observer $observer
    ) {

        $checkConfig = $this->scopeConfig->getValue('udexample_settings/display_payload_crqu/display_payload_crqu', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $message = $observer->getEvent()->getMessage();
        if ($checkConfig) {
            $session = $this->customerSession;
            $customerInfos = $session->getCustomer();
            $payload = array();
            $payload['websiteId'] = $customerInfos->getWebsiteId();
            $payload['created'] = $customerInfos->getCreatedAt();
            $payload['name'] = $customerInfos->getName();
            $payload['firstname'] = $customerInfos->getFirstname();
            $payload['lastname'] = $customerInfos->getLastname();
            $jsonData = json_encode($payload);
            $messageArray = $message->getMessageArray();
            $messageArray['messages']['request']['body']['payload']['customers'] = $jsonData;
            $message->setMessageArray($messageArray);
        }
    }

}
