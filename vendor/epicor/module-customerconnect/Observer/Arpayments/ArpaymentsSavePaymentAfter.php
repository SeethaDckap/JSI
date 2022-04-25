<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer\Arpayments;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;

class ArpaymentsSavePaymentAfter extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    protected $_request;
    protected $arpaymentsHelper;
    
    const PAYMENT_OBJECT_DATA_KEY = 'payment';
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;    
    
    public function __construct(\Magento\Framework\Registry $registry, 
                                PaymentTokenManagementInterface $paymentTokenManagement,
                                EncryptorInterface $encryptor,   
                                \Magento\Customer\Model\Session $customerSession,
                                \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
                                \Magento\Framework\App\Request\Http $request)
    {
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->_registry        = $registry;
        $this->_request         = $request;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->encryptor = $encryptor;       
        $this->customerSession = $customerSession;
    }
    
    
    
    public function execute(Observer $observer)
    {
        /** @var OrderPaymentInterface $payment */
        $payment = $observer->getDataByKey(self::PAYMENT_OBJECT_DATA_KEY);
        $extensionAttributes = $payment->getExtensionAttributes();

        $paymentToken = $this->getPaymentToken($extensionAttributes);
        if ($paymentToken === null) {
            return $this;
        }

        if ($paymentToken->getEntityId() !== null) {
            $this->paymentTokenManagement->addLinkToOrderPayment(
                $paymentToken->getEntityId(),
                $payment->getEntityId()
            );
            return $this;
        }
        $order = $payment->getOrder();
        if($order->getArpaymentsQuote()) {
            $customerId=$this->customerSession->getCustomerId(); 
        } else {
            $customerId = $order->getCustomerId();
        }

        $paymentToken->setCustomerId($customerId);
        $paymentToken->setIsActive(true);
        $paymentToken->setPaymentMethodCode($payment->getMethod());

        $additionalInformation = $payment->getAdditionalInformation();
        if (isset($additionalInformation[VaultConfigProvider::IS_ACTIVE_CODE])) {
            $paymentToken->setIsVisible(
                (bool) (int) $additionalInformation[VaultConfigProvider::IS_ACTIVE_CODE]
            );
        }

        $paymentToken->setPublicHash($this->generatePublicHash($paymentToken));

        $this->paymentTokenManagement->saveTokenWithPaymentLink($paymentToken, $payment);

        $extensionAttributes->setVaultPaymentToken($paymentToken);

        return $this;
    }    
    /**
     * Generate vault payment public hash
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    protected function generatePublicHash(PaymentTokenInterface $paymentToken)
    {
        $hashKey = $paymentToken->getGatewayToken();
        if ($paymentToken->getCustomerId()) {
            $hashKey = $paymentToken->getCustomerId();
        }

        $hashKey .= $paymentToken->getPaymentMethodCode()
            . $paymentToken->getType()
            . $paymentToken->getTokenDetails();

        return $this->encryptor->getHash($hashKey);
    }

    /**
     * Reads Payment token from Order Payment
     *
     * @param OrderPaymentExtensionInterface | null $extensionAttributes
     * @return PaymentTokenInterface | null
     */
    protected function getPaymentToken(OrderPaymentExtensionInterface $extensionAttributes = null)
    {
        if (null === $extensionAttributes) {
            return null;
        }

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $extensionAttributes->getVaultPaymentToken();

        if (null === $paymentToken || empty($paymentToken->getGatewayToken())) {
            return null;
        }

        return $paymentToken;
    }
}