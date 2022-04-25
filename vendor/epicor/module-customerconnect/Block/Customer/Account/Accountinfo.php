<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Account;


use Epicor\Customerconnect\Model\EccHidePrices\HidePrice as HidePrice;

class Accountinfo extends \Epicor\Customerconnect\Block\Customer\Info
{

    protected $_account_data;
    protected $_helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    private $hidePrice;

    public function __construct(
        HidePrice $hidePrice,
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = []
    )
    {
        $this->hidePrice = $hidePrice;
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->storeManager = $context->getStoreManager();
        parent::__construct(
            $context,
            $customerconnectHelper,
            $data
        );

        $details = $this->registry->registry('customer_connect_account_details');

        if ($details) {

            $accountData = $details->getAccount();

            $helper = $this->customerconnectHelper;

            $accountCurrency = $this->commMessagingHelper->getCurrencyMapping($accountData->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);

            // this section enables the default currency amount to be displayed in the account summary in brackets 
            $this->setBaseCurrencyCode($this->storeManager->getStore()->getBaseCurrencyCode());
            if ($this->getBaseCurrencyCode() != $accountCurrency) {
                $this->setConversionRequired(true);
                $baseCurrencyCode = $this->getBaseCurrencyCode();
                //$accountCurrencyCode = $accountData->getCurrencyCode();
//                $this->setConvertedCreditLimit($helper->getCurrencyConvertedAmount($accountData->getCreditLimit(), $accountData->getCurrencyCode(), $this->getBaseCurrencyCode()));
                if ($accountData->getBalance() != (0.00 && 0)) {
                    $balance = $helper->getCurrencyConvertedAmount($accountData->getBalance(), $accountCurrency);
//                    $this->setConvertedBalance($helper->getCurrencyConvertedAmount($accountData->getBalance(), $accountData->getCurrencyCode(), $this->getBaseCurrencyCode()));
                }
            }
//             if($this->getConvertedBalance()){
//                $this->_infoData = array(
//                    $this->__('Balance') => $balance." (".$this->getConvertedBalance().")"
//                );
//            }else{
            if (!$this->hidePrice->getHidePrices()) {
                $this->_infoData = array(
                    __('Balance')->render() => $helper->getCurrencyConvertedAmount($accountData->getBalance(), $accountCurrency)
                );
//            };

                if (is_null($accountData->getCreditLimit())) {
                    $credit_limit = __("No Limit");
                } else {
//                if ($this->getConvertedCreditLimit()) {
//                    $credit_limit = $helper->getCurrencyConvertedAmount($accountData->getCreditLimit(), $accountData->getCurrencyCode())
//                            . " (" . $this->getConvertedCreditLimit() . ")";
//                } else {
                    $credit_limit = $helper->getCurrencyConvertedAmount($accountData->getCreditLimit(), $accountCurrency);
//                };
                }
                $this->_infoData[__('Credit Limit')->render()] = $credit_limit;

                $this->_infoData[__('Unallocated Cash')->render()] = $helper->getCurrencyConvertedAmount($accountData->getUnallocatedCash(), $accountCurrency);
                $this->_infoData[__('Period to Date Purchases')->render()] = $helper->getCurrencyConvertedAmount($accountData->getPeriodToDatePurchases(), $accountCurrency);
                $this->_infoData[__('Current Year Purchases')->render()] = $helper->getCurrencyConvertedAmount($accountData->getCurrentYearPurchases(), $accountCurrency);
                $this->_infoData[__('Min Order Value')->render()] = ($accountData->getMinOrderValue() == ('0' || '')) ? 'N/A' : $helper->getCurrencyConvertedAmount($accountData->getMinOrderValue(), $accountCurrency);
            }
            if ($accountData->getLastPayment()) {
                //M1 > M2 Translation Begin (Rule 32)
                //$this->_infoData[$this->__('Last Payment Date')] = $accountData->getLastPayment()->getDate() ? $this->getHelper()->getLocalDate($accountData->getLastPayment()->getDate(), \Epicor\Common\Helper\Data::DAY_FORMAT_MEDIUM, false) : $this->__('N/A');
                $this->_infoData[__('Last Payment Date')->render()] = $accountData->getLastPayment()->getDate() ? $this->getHelper()->getLocalDate($accountData->getLastPayment()->getDate(), \IntlDateFormatter::MEDIUM, false) : __('N/A');
                //M1 > M2 Translation End
                if (!$this->hidePrice->getHidePrices()) {
                    $this->_infoData[__('Last Payment Value')->render()] = $helper->getCurrencyConvertedAmount($accountData->getLastPayment()->getValue(), $accountCurrency);
                }
            }
        }

        $this->setTitle(__('Account Information'));
        $this->setColumnCount(1);
        $this->setOnRight(true);
    }

}
