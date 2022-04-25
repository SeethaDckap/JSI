<?php

/**
 * UDExample
 * Copyright (C) 2019 
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

namespace Epicor\UDExample\Observer\Gor;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class RequestConvert2xmlBefore implements ObserverInterface {

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->scopeConfig = $context->getScopeConfig();
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

        $checkConfig = $this->scopeConfig->getValue('udexample_settings/display_conformance_checkout/display_conformance', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $gor = $observer->getEvent()->getMessage();
        /* @var $gor Epicor_Comm_Model_Message_Upload_Gor */
        $order = $gor->getOrder();
        /* @var $order Epicor_Comm_Model_Order */
        $order_payment = $order->getPayment();
        $additionalData = $order_payment->getAdditionalInformation();
        $isExist = isset($additionalData['conformance']);
        if ($checkConfig && $isExist) {
            $conformance = $additionalData['conformance'];
            $conformanceSelect = ($conformance) ? "Y" : "N";
            $xml = $gor->getMessageArray();
            $xml['messages']['request']['body']['userDefined']['u_conformance_c'] = $conformanceSelect;
            $gor->setMessageArray($xml);
        }
    }

}
