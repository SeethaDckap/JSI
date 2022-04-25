<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Pay\Model;


use Epicor\Pay\Helper\Data;
use Epicor\Pay\Helper\Data
\Magento\Framework\Model\ResourceModel\AbstractResource as AbstractResourceAlias;

class Pay extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = 'pay';
    protected $_formBlockType = 'Epicor\Pay\Block\Form\Pay';
    protected $_infoBlockType = 'Epicor\Pay\Block\Info\Pay';
    protected $_canCapture = true;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Comm\Model\Message\Request\AstFactory
     */
    protected $commMessageRequestAstFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;
    
    protected $arpaymentsHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Model\Message\Request\AstFactory $commMessageRequestAstFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->commMessageRequestAstFactory = $commMessageRequestAstFactory;
        $this->storeManager = $storeManager;
        $this->transactionFactory = $transactionFactory;
        $this->customer = $customer;
        $this->arpaymentsHelper = $arpaymentsHelper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }



    public function getMessage()
    {
        return $this->scopeConfig->getValue(
                'payment/pay/message',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * Is this payment method available
     * @param \Magento\Quote\Model\Quote $quote
     * @return boolean 
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $this->_registry->unregister('credit_check');
        $root = parent::isAvailable($quote);
        $handle = $this->arpaymentsHelper->checkArpaymentsPage();
        if($handle) {
            return false;
        }
        
        
        $helper = $this->commMessagingHelper;
        /* @var $helper \Epicor\Comm\Helper\Messaging */
        if($quote && $customerId = $quote->getCustomerId()){
           $customer = $this->customer->load($customerId);            
           $customerERPData = $customer->getCustomerErpAccount();
        }else{
            $customerERPData = $helper->getErpAccountInfo();
        }
        if (
            !$customerERPData ||
            $customerERPData->getId() == $this->scopeConfig->getValue('customer/create_account/default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ||
            !$customerERPData->checkCustomertype()
        ) {
            $root = false;
        }

        if ($root) {
            $controller = ucfirst($this->request->getControllerName());
            $action = $this->request->getActionName();

            if (($controller == 'Onepage' && in_array($action, array('index', 'saveOrder'))) ||
                ($controller == 'Multishipping' && in_array($action, array('index', 'billing')))) {
                if ($this->scopeConfig->getValue('epicor_comm_enabled_messages/ast_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                    $ast = $this->commMessageRequestAstFactory->create();
                    /* @var $ast \Epicor\Comm\Model\Message\Request\Ast */
                    $ast->setCustomerGroupId($customerERPData->getId());
                    $ast->sendMessage();
                }
            }

            $customerERPData = $helper->getErpAccountInfo();
            if ($customerERPData->getOnstop() || !$customerERPData->hasCurrencyCode($this->storeManager->getStore()->getBaseCurrencyCode())) {
                $this->_registry->register('credit_check', 1); 
                $root = false;
            } else if ($this->scopeConfig->isSetFlag('payment/pay/creditavailablecheck', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $customerCurrencyData = $customerERPData->getCurrencyData($this->storeManager->getStore()->getBaseCurrencyCode());

                if (is_null($customerCurrencyData->getCreditLimit())) {
                    $root = true;
                } else {
                    $availableCredit = $customerCurrencyData->getAvailableCredit();
                    $creditLimit = $customerCurrencyData->getCreditLimit();
                    if ((int) $availableCredit && (int) $creditLimit) {
                        $creditLimit = $availableCredit;
                    } else {
                        $creditLimit = $creditLimit - ($customerCurrencyData->getBalance() - $customerCurrencyData->getUnallocatedCash());
                    }
                    if ($quote && ($quote instanceof \Magento\Framework\DataObject) && !$this->scopeConfig->isSetFlag('payment/pay/decrementpurchases', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {       // if not allowed to go into -ve credit by order
                        // instanceof included to stop billingagreements error
                        $orderValue = $quote->getSubtotalWithDiscount();
                        $currentCredit = $creditLimit - $orderValue;
                    } else {
                        $currentCredit = $creditLimit;
                    }

                    if (!$this->scopeConfig->isSetFlag('payment/pay/softcreditlimit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $currentCredit < 1) {
                        $root = false;
                        $this->_registry->register('credit_check', 1);
                    }
                }
            }
        }

        return $root;
    }
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        /* @var $order \Magento\Sales\Model\Order */
        if ($order->canInvoice()) {
            $invoice = $order->prepareInvoice();

            $invoice->register()->capture();
            $this->transactionFactory->create()
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
        }
    }


    public function validate()
    {
        parent::validate();

        return $this;
    }

}
