<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Plugin\Checkout;

use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionFactory;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Quote\Model\QuoteRepository;
use Epicor\OrderApproval\Model\GroupManagement;

/**
 * One page checkout processing model
 */
class ShippingInformationManagementPlugin
{

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var PaymentDetailsExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var GroupManagement
     */
    private $groupManagement;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * ShippingInformationManagementPlugin constructor.
     *
     * @param QuoteRepository                $quoteRepository
     * @param PaymentDetailsExtensionFactory $extensionFactory
     * @param GroupManagement                $groupManagement
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        PaymentDetailsExtensionFactory $extensionFactory,
        GroupManagement $groupManagement,
        \Magento\Framework\Registry $registry
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->extensionFactory = $extensionFactory;
        $this->groupManagement = $groupManagement;
        $this->registry = $registry;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param \Closure                      $proceed
     * @param                               $cartId
     * @param ShippingInformationInterface  $addressInformation
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundSaveAddressInformation(
        ShippingInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $returnValue = $proceed($cartId, $addressInformation);
        $quote = $this->quoteRepository->getActive($cartId);
        $group = $this->groupManagement->getAppliedGroupByQuote($quote);
        $budget = $this->groupManagement->getAppliedBudget();
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

        if (!$group && $budget && !$budget->getIsAllowCheckout()) {

            //Different message for shopper and ERP budget.
            if ($budget->getErpId()) {
                $message
                    = __("You cannot proceed with checkout as your order exceeds the budget limit set for your company. Go to Customer Connect>Dashboard to review account budget limit information.");
            } else {
                $message
                    = __("You cannot proceed with checkout as your order exceeds the budget limit set for you. Go to My Account to review your budget limit information.");
            }

            $this->registry->register('bsv_quote_error', $message);

            throw new LocalizedException($message);
        }

        return $returnValue;
    }

}
