<?php

/**
 * UDExample
 * Copyright (C) 2017 
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

use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Payment\Model\InfoInterface;

class TestAssigData extends AbstractDataAssignObserver {

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

        if ($checkConfig) {
            $data = $this->readDataArgument($observer);
            $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
            if (!is_array($additionalData)) {
                return;
            }
            $additionalData = new DataObject($additionalData);
            $paymentMethod = $this->readMethodArgument($observer);
            $con = $additionalData->getData('conformance');
            if (isset($con)) {
                $payment = $observer->getPaymentModel();
                if (!$payment instanceof InfoInterface) {
                    $payment = $paymentMethod->getInfoInstance();
                }
                if ($payment instanceof InfoInterface) {
                    $payment->setAdditionalInformation('conformance', $con);
                }
            }
        }
    }

}
