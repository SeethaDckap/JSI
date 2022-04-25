<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Plugin\Payment;

use Magento\Payment\Model\MethodList as PaymentMethodList;
use Epicor\Comm\Model\Erp\Mapping\PaymentFactory as PaymentMappingFactory;
use Epicor\OrderApproval\Model\GroupManagement as GroupManagementFactory;
use Epicor\OrderApproval\Model\GroupManagement as GroupManagement;

class MethodList
{
    /**
     * @var GroupManagementFactory
     */
    private $groupManagementFactory;

    /**
     * @var PaymentMappingFactory
     */
    private $paymentMappingFactory;

    /**
     * MethodList constructor.
     *
     * @param GroupManagementFactory $groupManagementFactory
     * @param PaymentMappingFactory  $paymentMappingFactory
     */
    public function __construct(
        GroupManagementFactory $groupManagementFactory,
        PaymentMappingFactory $paymentMappingFactory
    ) {
        $this->groupManagementFactory = $groupManagementFactory;
        $this->paymentMappingFactory = $paymentMappingFactory;
    }

    /**
     * @param PaymentMethodList                          $subject
     * @param array                                      $availableMethods
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     *
     * @return array
     */
    public function afterGetAvailableMethods(
        PaymentMethodList $subject,
        array $availableMethods,
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        /** @var GroupManagement $groupManagement */
        $groupManagement = $this->groupManagementFactory;

        $group = $groupManagement->getAppliedGroupByQuote($quote);
        if($group) {
            foreach ($availableMethods as $key => $method) {
                /** @var \Epicor\Comm\Model\Erp\Mapping\Payment $paymentMapping */
                $paymentMapping = $this->paymentMappingFactory->create();
                $paymentMappingModel = $paymentMapping->loadMappingByStore($method->getCode(), 'magento_code');
                if($paymentMappingModel->getPaymentCollected() == "C") {
                    unset($availableMethods[$key]);
                }
            }
        }

        return $availableMethods;
    }
}