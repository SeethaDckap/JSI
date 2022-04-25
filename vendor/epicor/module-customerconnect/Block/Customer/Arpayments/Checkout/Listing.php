<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Checkout;


use Magento\Framework\View\Element\Template;

/**
 * Customer Orders list
 */
class Listing extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;  
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;    
    
    /**
     * Payment Model Config
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;    
    
    protected $arpaymentsHelper;       
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;      
    
    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;    
     
    
    public function __construct(
        Template\Context $context, 
        \Magento\Payment\Helper\Data $paymentHelper, 
        \Magento\Payment\Model\Config $paymentConfig,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        \Epicor\Customerconnect\Model\ArPayment\Session $checkoutSession,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = array())
    {
        $this->paymentHelper = $paymentHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_paymentConfig = $paymentConfig;
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->checkoutSession = $checkoutSession;
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct($context, $data);
    }


    
    public function getActivePaymentMethods()
    {
        $payments = $this->_paymentConfig->getActiveMethods();
        $methods = array();
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->scopeConfig->getValue('payment/'.$paymentCode.'/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $methods[$paymentCode] = array(
                'label' => $paymentTitle,
                'value' => $paymentCode
            );
        }
        return $methods;
    }
    
    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfigForPayments()
    {
        return $this->scopeConfig->getValue("customerconnect_enabled_messages/CAAP_request/valid_payment_methods", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }    
    
    
    public function getCurrencySymbol()
    {
        $helper = $this->arpaymentsHelper;
       return $currency_symbol =$helper->getCurrencySymbol();            
    }
    
    public function getArCheckoutQuote()
    {
        return $this->checkoutSession;
    }
    
    public function Combinevalues($postParams) {
        $insertedVals = json_decode($postParams, true);
        return $insertedVals;
    }  
    
    public function formatPriceForArPayment($price) {
        return number_format($price, '2', '.', '');
    }

    /**
     * 
     * Get processed date
     * @param string
     * @return string
     */
    public function processDate($rawDate=NULL)
    {
        if ($rawDate) {
            $timePart = substr($rawDate, strpos($rawDate, "T") + 1);
            if (strpos($timePart, "00:00:00") !== false) {
                $processedDate = $this->customerconnectHelper->getLocalDate($rawDate, \IntlDateFormatter::MEDIUM, false);
            } else {
                $processedDate = $this->customerconnectHelper->getLocalDate($rawDate, \IntlDateFormatter::MEDIUM, false);
            }
        } else {
            $processedDate = '';
        }
        return $processedDate;
    }     
        
}