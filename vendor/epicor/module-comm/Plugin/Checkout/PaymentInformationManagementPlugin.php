<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Checkout;
use Magento\Framework\Exception\InputException;

/**
 * One page checkout processing model for login Checkout
 * set Addition Set Of new Ecc Field Data
 * like Order Comment And Additional Reference
 */
class PaymentInformationManagementPlugin
{

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * PaymentInformationManagementPlugin constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->registry = $registry;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return mixed
     */
    public function aroundSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $comment = "";
        $extensionAttributes = $paymentMethod->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getComment()) {
            $comment = trim($extensionAttributes->getComment());
        }

        // process around
        $result = $proceed($cartId, $paymentMethod, $billingAddress);
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
    )
    {
        $comment = "";

        $eccAdditionalReference = "";
        $extensionAttributes = $paymentMethod->getExtensionAttributes();
        if ($extensionAttributes) {
            if ($extensionAttributes->getComment()) {
                $comment = trim($extensionAttributes->getComment());
            }
            if ($extensionAttributes->getEccAdditionalReference()) {
                $eccAdditionalReference = $extensionAttributes->getEccAdditionalReference();
            }
        }
        if ($billingAddress) {
            if ($billingAddress->getCustomerAddressId()) {
                $billingAddressdata = $this->addressFactory->create()->load($billingAddress->getCustomerAddressId());
                $billingAddress->setEccErpAddressCode($billingAddressdata->getEccErpAddressCode());
            } else {
                $defaultAddressCode = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_address_code',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $billingAddress->setEccErpAddressCode($defaultAddressCode);
            }
        }
        $quote = $this->quoteRepository->getActive($cartId);

        //set Additional Reference
        $quote->setEccAdditionalReference($eccAdditionalReference);
        //set Customer Order Comment
        $quote->setCustomerNote($comment);

        if ($extensionAttributes && $extensionAttributes->getEccCustomerOrderRef() && $paymentMethod->getMethod() == 'pay') {
            $epmpo = $extensionAttributes->getEccCustomerOrderRef();
            $quote->setEccCustomerOrderRef($epmpo);
        }

        // process around
        try {
            $result = $proceed($cartId, $paymentMethod, $billingAddress);
            if (!$this->registry->registry('isTriggerBsv909Error')) {
                $this->registry->register('isTriggerBsv909Error', true);
            }
        } catch (\Exception $ex) {
            throw new InputException(__($ex->getMessage()));
            return;
        }

        return $result;
    }
}
