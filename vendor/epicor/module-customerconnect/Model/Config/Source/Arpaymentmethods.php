<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Config\Source;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Payment\Model\Config;

class Arpaymentmethods
{
    
    /**
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;
    /**
     * @var Config
     */
    protected $_paymentModelConfig;
    
    /**
     * Payment Helper Data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;    
    
    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\PaymentmethodsFactory
     */
    protected $commErpMappingPaymentmethodsFactory;    
     
    /**
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     * @param Config               $paymentModelConfig
     */
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        \Epicor\Comm\Model\Erp\Mapping\PaymentmethodsFactory $commErpMappingPaymentmethodsFactory,
        \Magento\Payment\Helper\Data $paymentHelper,
        Config $paymentModelConfig
    ) {
 
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->commErpMappingPaymentmethodsFactory = $commErpMappingPaymentmethodsFactory;
        $this->_paymentModelConfig = $paymentModelConfig;
        $this->_paymentHelper = $paymentHelper;
    }    

    public function toOptionArray()
    {
        $payments = $this->commErpMappingPaymentmethodsFactory->create()->toOptionArray();
        $methods = array();
        foreach ($payments as $paymentCode => $paymentModel) {
            if($paymentModel['value'] !="pay") {
                $methods[$paymentModel['value']] = array(
                    'label' => $paymentModel['label'],
                    'value' => $paymentModel['value'],
                );
            }
        }
        return $methods;
    }

}