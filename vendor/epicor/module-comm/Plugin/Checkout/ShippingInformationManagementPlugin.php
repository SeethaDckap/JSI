<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Checkout;

use Magento\Framework\Exception\InputException;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionFactory;
/**
 * One page checkout processing model
 */
class ShippingInformationManagementPlugin
{

    protected $quoteRepository;
    protected $commMessageRequestBsvFactory;

    protected $scopeConfig;
    protected $addressExtension;
    protected $addressFactory;

    /**
     * @var \Epicor\Comm\Helper\Cart\SendbsvFactory
     */
    protected $sendBsvHelperFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Pay\Helper\Data
     */
    protected $payHelper;

    /**
     * @var PaymentDetailsExtensionFactory
     */
    protected $extensionFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Model\Message\Request\BsvFactory $commMessageRequestBsvFactory,
        \Magento\Quote\Api\Data\AddressExtensionFactory $addressExtension,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Epicor\Comm\Helper\Cart\SendbsvFactory $sendBsvHelperFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Pay\Helper\Data $payHelper,
        PaymentDetailsExtensionFactory $extensionFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->commMessageRequestBsvFactory = $commMessageRequestBsvFactory;
        $this->scopeConfig = $scopeConfig;
        $this->addressExtension = $addressExtension;
        $this->addressFactory = $addressFactory;
        $this->sendBsvHelperFactory = $sendBsvHelperFactory;
        $this->registry = $registry;
        $this->payHelper = $payHelper;
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {

        $address = $addressInformation->getShippingAddress();
        $extAttributes = $address->getExtensionAttributes();
        $orderRef = $extAttributes->getEccCustomerOrderRef();
        $ShipStatus = $extAttributes->getEccShipStatusErpcode() ?: "";
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setEccCustomerOrderRef($orderRef);
        $quote->setEccShipStatusErpcode($ShipStatus);
        $dda = $extAttributes->getEccRequiredDate();
        $taxExmptRef = $extAttributes->getEccTaxExemptReference()?:'';
        if($dda){
            $quote->setEccRequiredDate($dda);
            $quote->setEccIsDdaDate(1);
        } else {
            $quote->setEccRequiredDate('');
            $quote->setEccIsDdaDate(0);
        }
        $quote->setEccTaxExemptReference($taxExmptRef);
        if($address->getCustomerAddressId()){
            $addressdata = $this->addressFactory->create()->load($address->getCustomerAddressId());
            $address->setEccErpAddressCode($addressdata->getEccErpAddressCode());
        }else{
            $defaultAddressCode = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_address_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $address->setEccErpAddressCode($defaultAddressCode);
        }
        $addressInformation->setShippingAddress($address);
        $quote->setEccBsvCarriageAmount(null);
        $quote->setEccBsvCarriageAmountInc(null);
        $quote->getShippingAddress()->setEccBsvCarriageAmount(null);
        $quote->getShippingAddress()->setEccBsvCarriageAmountInc(null);
        $quote->getShippingAddress()->setEccBsvGrandTotal(null);
        $quote->getShippingAddress()->setEccBsvGrandTotalInc(null);
    }

    /**
     *
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param \Closure $proceed
     * @param type $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return type
     * @throws InputException
     */
    public function aroundSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $this->registry->unregister('QuantityValidatorObserver');
        $this->registry->register('QuantityValidatorObserver', 1);
        try {
            $returnValue = $proceed($cartId, $addressInformation);
        } catch (\Exception $ex) { // custom message should add if bsv responce getting error
            $bsvErrorMessage = $this->registry->registry('bsv_quote_error');
            if ($bsvErrorMessage) {
                throw new InputException(__($bsvErrorMessage));
                return;
            }
        }
        $creditCheck = $this->registry->registry('credit_check');
        if ($creditCheck) {
            $creditCheckMsg = $this->payHelper->getCreditCheckMessage();
            if (is_string($creditCheckMsg)) {
                $extensionAttributes = $returnValue->getExtensionAttributes() ?: $this->extensionFactory->create();
                $extensionAttributes->setEccPayError($creditCheckMsg);
                $returnValue->setExtensionAttributes($extensionAttributes);
            }
        }
        $this->registry->unregister('QuantityValidatorObserver');
        return $returnValue;
    }

}
