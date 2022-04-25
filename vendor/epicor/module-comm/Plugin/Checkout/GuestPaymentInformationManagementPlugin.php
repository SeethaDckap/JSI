<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Checkout;

use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
/**
 * One page checkout processing model for guest checkout
 * set Addition Set Of new Ecc Field Data
 * like Order Comment And Additional Reference
 */
class GuestPaymentInformationManagementPlugin
{

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * GuestPaymentInformationManagementPlugin constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Framework\Registry $registry
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\Registry $registry,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->registry = $registry;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @param \Magento\Checkout\Model\GuestPaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return mixed
     */
    public function aroundSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\GuestPaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $comment = "";
        $extensionAttributes = $paymentMethod->getExtensionAttributes();
        if ($extensionAttributes->getComment()) {
            $comment = trim($extensionAttributes->getComment());
        }

        $result = $proceed($cartId, $email, $paymentMethod, $billingAddress);
        $this->registry->unregister('isTriggerBsv909Error');

        if ($result) {
            $order = $this->orderRepository->get($result);
            $order->setHistoryEntityName('order');
            $history = $order->addStatusHistoryComment($comment);
            $history->save();
            $order->setCustomerNote($comment);
        }

        return $result;
    }

    /**
     * @param \Magento\Checkout\Model\GuestPaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundSavePaymentInformation(
        \Magento\Checkout\Model\GuestPaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {

        $extensionAttributes = $paymentMethod->getExtensionAttributes();
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        /** @var $quoteIdMask QuoteIdMask */
        $quoteId = $quoteIdMask->getQuoteId();
        $quote = $this->quoteRepository->getActive($quoteId);
        /** @var \Magento\Quote\Model\Quote $quote */

        $comment = "";
        $eccAdditionalReference = "";
        if ($extensionAttributes) {
            if ($extensionAttributes->getComment()) {
                $comment = trim($extensionAttributes->getComment());
            }
            if ($extensionAttributes->getEccAdditionalReference()) {
                $eccAdditionalReference = $extensionAttributes->getEccAdditionalReference();
            }
        }

        //set Additional Reference
        $quote->setEccAdditionalReference($eccAdditionalReference);
        //set Customer Order Comment
        $quote->setCustomerNote($comment);

        $result = $proceed($cartId, $email, $paymentMethod, $billingAddress);
        if (!$this->registry->registry('isTriggerBsv909Error')) {
            $this->registry->register('isTriggerBsv909Error', true);
        }
        return $result;
    }
}