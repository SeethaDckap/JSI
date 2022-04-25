<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Epicor\OrderApproval\Model\GroupManagementFactory as GroupManagementFactory;

/**
 * Class responsive for sending order approval emails when it's created through storefront.
 */
class SaveBranchPickupInformation implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GroupManagementFactory
     */
    private $groupManagementFactory;

    /**
     * SaveBranchPickupInformation constructor.
     *
     * @param LoggerInterface        $logger
     * @param GroupManagementFactory $groupManagementFactory
     */
    public function __construct(
        LoggerInterface $logger,
        GroupManagementFactory $groupManagementFactory


    ) {
        $this->logger = $logger;
        $this->groupManagementFactory = $groupManagementFactory;
    }

    /**
     * Send order approval email.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        /** @var GroupManagement $groupManagement */
        $groupManagement = $this->groupManagementFactory->create();
        $group = $groupManagement->getAppliedGroupByQuote($quote);

        if ($group) {
            $paymentMethods = $observer->getEvent()->getExtensionAttributes();
            $paymentMethods->setIsApprovalRequire(__("This order will be under review and the order will later be approved by the approver ."));
        }
    }
}
