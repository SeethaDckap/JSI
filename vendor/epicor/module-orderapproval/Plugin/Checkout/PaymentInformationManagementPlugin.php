<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Plugin\Checkout;

use Magento\Framework\Exception\InputException;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionFactory;
use Epicor\OrderApproval\Model\GroupManagementFactory as GroupManagementFactory;
use Epicor\OrderApproval\Model\GroupManagement;
use Magento\Quote\Model\QuoteRepository;
use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * One page checkout processing model
 */
class PaymentInformationManagementPlugin
{

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var PaymentDetailsExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var GroupManagementFactory
     */
    private $groupManagementFactory;

    /**
     * PaymentInformationManagementPlugin constructor.
     *
     * @param QuoteRepository                $quoteRepository
     * @param PaymentDetailsExtensionFactory $extensionFactory
     * @param GroupManagementFactory         $groupManagementFactory
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        PaymentDetailsExtensionFactory $extensionFactory,
        GroupManagementFactory $groupManagementFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->extensionFactory = $extensionFactory;
        $this->groupManagementFactory = $groupManagementFactory;
    }

    /**
     * @param PaymentInformationManagement $subject
     * @param \Closure                     $proceed
     * @param                              $cartId
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundGetPaymentInformation(
        PaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId
    ) {
        $returnValue = $proceed($cartId);
        $quote = $this->quoteRepository->getActive($cartId);

        /** @var GroupManagement $groupManagement */
        $groupManagement = $this->groupManagementFactory->create();
        $group = $groupManagement->getAppliedGroupByQuote($quote);
        $budget = $groupManagement->getAppliedBudget();
        if ($group && !$budget) {
            $extensionAttributes = $returnValue->getExtensionAttributes()
                ?: $this->extensionFactory->create();
            $extensionAttributes->setIsApprovalRequire(__("This order will be under review and the order will later be approved by the approver."));
            $returnValue->setExtensionAttributes($extensionAttributes);
        }

        if ($group && $budget) {
            $extensionAttributes = $returnValue->getExtensionAttributes()
                ?: $this->extensionFactory->create();
            $extensionAttributes->setIsApprovalRequire(__("This order exceeds your budget limit and is subject to approval for further processing."));
            $returnValue->setExtensionAttributes($extensionAttributes);
        }

        return $returnValue;
    }

    /**
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundSavePaymentInformation(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {

        $quote = $this->quoteRepository->getActive($cartId);
        /** @var GroupManagement $groupManagement */
        $groupManagement = $this->groupManagementFactory->create();
        $group = $groupManagement->getAppliedGroupByQuote($quote);
        if ($group) {
            $quote->setIsApprovalPending(1);
        }

        // process around
        try {
            $result = $proceed($cartId, $paymentMethod, $billingAddress);
        } catch (\Exception $ex) {
            throw new InputException(__($ex->getMessage()));
            return;
        }

        return $result;
    }

}
