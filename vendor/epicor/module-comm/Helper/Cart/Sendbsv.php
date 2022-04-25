<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Helper\Cart;

use Magento\Framework\App\ObjectManager;
use Epicor\Customerconnect\Helper\Arpayments;

class Sendbsv extends \Epicor\Comm\Helper\Data
{

    /**
     * @var \Epicor\Comm\Model\Message\Request\BsvFactory
     */
    private $commMessageRequestBsvFactory;

    /**
     * @var \Epicor\Customerconnect\Helper\Arpayments
     */
    private $arpaymentsHelper;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $currency;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * Customer Address repository
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory
     */
    protected $commCustomerErpaccountAddressFactory;

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    private $branchPickupHelper;


    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Epicor\Comm\Model\Message\Request\BsvFactory $commMessageRequestBsvFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        Arpayments $arpaymentsHelper = null
    )
    {
        parent::__construct($context);
        $this->commMessageRequestBsvFactory = $commMessageRequestBsvFactory;
        $this->arpaymentsHelper = $arpaymentsHelper ?: ObjectManager::getInstance()->get(Arpayments::class);
        $this->currency = $currency;
        $this->directoryHelper = $context->getDirectoryHelper();
        $this->addressRepository = $addressRepository;
        $this->cookieManager = $cookieManager;
        $this->commCustomerErpaccountAddressFactory = $commCustomerErpaccountAddressFactory;
        $this->branchPickupHelper = $branchPickupHelper;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param bool $force
     * @return \Magento\Quote\Model\Quote
     */
    public function sendCartBsv(\Magento\Quote\Model\Quote $quote, $force = false)
    {

        if (!$quote->getShippingAddress()->getCountryId()) {
            $countryId = $this->directoryHelper->getDefaultCountry();
            $quote->getShippingAddress()->setCountryId($countryId);
        }
        $customerSession = $this->customerSessionFactory->create();
        $selectBranchcode = false;
        if ($this->branchPickupHelper->isBranchPickupAvailable()) {
            $selectBranchcode = $this->branchPickupHelper->getSelectedBranch();
        }
        //Set default address to  fresh cart
        if (!$selectBranchcode && $customerSession->isLoggedIn() && $customerSession->getCustomer()->isSalesRep()) {
            if (!$this->cookieManager->getCookie('erp_shipping_customer_addressId') &&
                !$quote->getShippingAddress()->getStreetFull()) {
                $defaultShippingAddress = $customerSession->getCustomer()->getPrimaryShippingAddress();
                $addresssalesrep = $quote->getShippingAddress();
                $addressData = $defaultShippingAddress->getData();
                $addresssalesrep->addData($addressData);
                $this->cookieManager->setPublicCookie('erp_shipping_customer_addressId', $defaultShippingAddress->getId());
                $addresssalesrep->setCustomerAddressId(null);
                $quote->setShippingAddress($addresssalesrep);
                $quote->getShippingAddress()->setEccErpAddressCode($defaultShippingAddress->getEccErpAddressCode());
            }
        }
        if ($force || $this->canBsvBeSent($quote)) {
            if ($quote->getIsMultiShipping()) {
                $this->_sendMultishippingBsv($quote);
            } else {
                if ($this->request->getActionName() === 'reorder' && !$quote->hasItems()) {
                    return $quote;
                }
                //Set default address to  fresh cart
                if (!$selectBranchcode && $customerSession->isLoggedIn() && !$customerSession->getCustomer()->isSalesRep() &&
                    (!$quote->getShippingAddress()->getCustomerAddressId() && !$quote->getShippingAddress()->getStreetFull())) {
                    $defaultShippingAddress = $customerSession->getCustomer()->getDefaultShippingAddress();
                    $address = $quote->getShippingAddress();
                    $addressData = $defaultShippingAddress->getData();
                    $address->addData($addressData);
                    $address->setCustomerAddressId($defaultShippingAddress->getId());
                    $quote->setShippingAddress($address);
                    $quote->getShippingAddress()->setEccErpAddressCode($defaultShippingAddress->getEccErpAddressCode());
                }
                $quote = $this->_sendBsv($quote, $quote->getShippingAddress());
            }

            $totalData = $this->_getQuoteTotals($quote);
            $customerSession->setBsvTriggerTotals($totalData);
        }

        return $quote;
    }

    /**
     *
     * @param \Epicor\Comm\Model\Quote $quote
     *
     */
    private function _sendMultishippingBsv($quote)
    {
        $addresses = $quote->getAllShippingAddresses();
        foreach ($addresses as $address) {
            /* @var $address \Magento\Quote\Model\Quote\Address */
            $this->_sendBsv($quote, $address);
        }
    }

    /**
     * @param $quote
     * @param $address
     * @return \Epicor\Comm\Model\Quote
     * @throws \Exception
     */
    private function _sendBsv($quote, $address)
    {
        //don't send bsv if there are non erp products in cart
        // skip bsv for ARPayment when non erp product config is enable
        if (!$this->arpaymentsHelper->checkArpaymentsPage() && !$this->cartContainsNonErpProducts()) {
            $bsv = $this->commMessageRequestBsvFactory->create();
            /* @var $bsv \Epicor\Comm\Model\Message\Request\Bsv */
            $bsvForCart = $this->scopeConfig->getValue('epicor_comm_enabled_messages/bsv_request/bsv_for_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($bsv->isActive() && $bsvForCart) {
                $bsv->setQuote($quote);
                $bsv->setShippingAddress($address);
                $bsv->setPromotions(false);
                $bsv->setSaveChanges(true);
                if ($bsv->sendMessage()) {
                    if (!$this->registry->registry('bsv_sent')) {
                        $this->registry->register('bsv_sent', true);
                    }
                    $quote = $bsv->getQuote();
                    if (!$quote->getIsMultiShipping()) {
                        $quote->setTotalsCollectedFlag(false);
                        $quote->collectTotals()->save();
                    }
                }
            }
        }

        return $quote;
    }

    private function canBsvBeSent($quote)
    {
        $sendBsv = false;
        $module = $this->request->getModuleName();

        if (
            $this->registry->registry('bsv-processing') ||
            $module == 'erpsimulator' ||
            $this->registry->registry('bsv_sent') ||
            $this->registry->registry('dont_send_bsv')
        ) {
            return $sendBsv;
        }


        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();

        if ($controller == 'onepage' && ($action == 'savePayment' || $action == 'saveBilling')) {
            $sendBsv = true;
        } else if ($module == 'multishipping' && $controller == 'checkout' && ($action == 'overview' || $action == 'addressesPost')) {
            $sendBsv = true;
        } else if ($this->_hasCartChanged($quote) && $module != 'multishipping') {
            $sendBsv = true;
        }

        if ($this->registry->registry('ecc_appply_coupon')) {
            $sendBsv = true;
        }

        return $sendBsv;
    }

    /**
     *
     * @param \Epicor\Comm\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address $address
     *
     * @return boolean
     */
    private function _hasCartChanged($quote)
    {
        $changed = true;
        $diff = $this->_getCartChanges($quote);

        if (empty($diff)) {
            $changed = false;
        }

        return $changed;
    }

    /**
     *
     * @param \Epicor\Comm\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address $address
     *
     * @return boolean
     */
    private function _preventTotals($quote, $address, $total)
    {
        $prevent = false;
        $diff = $this->_getDifferences($quote, $address, $total);

        if (isset($diff['item_list']) || isset($diff['shipping_method']) || isset($diff['discount']) || isset($diff['shipping'])) {
            $prevent = true;
        }

        if ($quote->getIsMultiShipping()) {
            $prevent = true;
        }

        return $prevent;
    }

    private function _getCartChanges($quote)
    {
        $diff = array();
        $totalData = $this->_getQuoteTotals($quote);
        $sessionTotals = $this->customerSessionFactory->create()->getBsvTriggerTotals();

        if (!empty($sessionTotals)) {
            $diff = array_diff_assoc($totalData, $sessionTotals);
        } else {
            $diff = $totalData;
        }

        return $diff;
    }

    /**
     * Gets the current totals for the quote / address
     *
     * @param \Epicor\Comm\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address $address
     *
     * @return array
     */
    private function _getQuoteTotals($quote)
    {
        $totalData = array();

        $totalData['discount'] = $quote->getDiscountAmount();
        $totalData['subtotal'] = $quote->getSubtotal();
        $totalData['grand'] = $quote->getGrandTotal();
        $totalData['shipping'] = $quote->getShippingAddress()->getShippingAmount();
        if ($quote->getShippingAddress()->getShippingMethod()) {
            $totalData['shipping_method'] = $quote->getShippingAddress()->getShippingMethod();
        }

        $itemString = '';
        $salesRepString = '';
        $contractString = '';
        $items = [];
        foreach ($quote->getAllItems() as $item) {
            $items[$item->getId()] = $item;
        }
        if(!empty($items)) {
            ksort($items);
        }
        foreach ($items as $item) {
            /* @var $item Mage_Sales_Model_Quote_Item */
            $itemString .= $item->getSku() . 'x' . $item->getQty() . '#';
            $salesRepString .= $this->currency->format($item->getEccSalesrepPrice(), ['display' => \Zend_Currency::NO_SYMBOL], false) . '|' .
                $this->currency->format($item->getEccSalesrepDiscount(), ['display' => \Zend_Currency::NO_SYMBOL], false) . '#';
            $contractString .= $item->getEccContractCode() . '#';
        }

        $totalData['item_list'] = $itemString;
        $totalData['item_salesrep'] = $salesRepString;
        $totalData['item_contract'] = $contractString;
        $totalData['customer_ref'] = $quote->getEccCustomerOrderRef();
        $totalData['contract_code'] = $quote->getEccContractCode();

        $totalData['shipping_address'] = $this->getShippingAddress($quote);
        $totalData['shipping_address_country'] = $quote->getShippingAddress()->getCountry();
        $totalData['shipping_address_postcode'] = $quote->getShippingAddress()->getPostcode();

        $region = $quote->getShippingAddress()->getRegion();
        $totalData['shipping_address_region'] = (is_array($region) && isset($region['region'])) ? $region['region'] : $region;

        /* $triggers = explode(',', Mage::getStoreConfig('epicor_comm_enabled_messages/bsv_request/bsv_triggers'));

          if (in_array('payment_method', $triggers)) {
          $totalData['payment_method'] = $quote->getPayment()->getMethod();
          }

          if (in_array('billing_address', $triggers)) {
          $billing = $quote->getBillingAddress();
          $billingAddress = $billing->format('flat');
          $totalData['billing_address'] = $billingAddress;
          }

          if (in_array('shipping_address', $triggers)) {
          $shipping = $quote->getShippingAddress();
          $shippingAddress = $shipping->format('flat');
          $totalData['shipping_address'] = $shippingAddress;
          }
         */

        return $totalData;
    }

    /**
     * @param $quote \Epicor\Comm\Model\Quote
     * @return int|mixed|null
     */
    private function getShippingAddress($quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress instanceof \Epicor\Comm\Model\Quote\Address && $shippingAddress->getCustomerAddressId()) {
            return $shippingAddress->getCustomerAddressId();
        }

        return '';
    }
}
