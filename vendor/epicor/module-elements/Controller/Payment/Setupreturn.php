<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Controller\Payment;

/**
 * Elements Payment  
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 */
class Setupreturn extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Epicor\Elements\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $elementsTransactionCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    protected $layoutFactory;
    
    
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;       


    protected $onepage;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Elements\Model\ResourceModel\Transaction\CollectionFactory $elementsTransactionCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Epicor\Elements\Logger\Logger $logger,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Checkout\Model\Type\Onepage $onepage

    ) {
        $this->checkoutSession = $checkoutSession;
        $this->elementsTransactionCollectionFactory = $elementsTransactionCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->resultRawFactory = $resultRawFactory;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->onepage = $onepage;
        $this->layoutFactory = $layoutFactory;
        parent::__construct(
            $context
        );
    }


    public function execute()
    {
        $this->_expireAjax();

        $data                  = $this->getRequest()->getParams();
        $transactionCollection = $this->elementsTransactionCollectionFactory->create();
        $elements              = $transactionCollection->addFieldtoFilter(
            'transaction_setup_id',
            @$data['TransactionSetupID']
        )->getFirstItem();

        if ($elements->getId()) {
            $elements->setHostedPaymentStatus(@$data['HostedPaymentStatus'])
                ->setHostedExpressResponseCode(@$data['ExpressResponseCode'])
                ->setHostedExpressResponseMessage(@$data['ExpressResponseMessage'])
                ->setHostedServicesId(@$data['ServicesID'])
                ->setHostedValidationCode(@$data['ValidationCode'])
                ->setCreditCardAuthHostResponseCode(@$data['ExpressResponseCode'])
                ->setCreditCardAuthHostResponseMessage(@$data['ExpressResponseMessage'])
                ->setBillingAddress(@$data['BillingAddress1'])
                ->setBillingZipcode(@$data['BillingZipcode'])
                ->setPaymentAccountId(@$data['PaymentAccountID'])
                ->setLastFour(@$data['LastFour'])
                ->setAvsResponseCode(@$data['AVSResponseCode'])
                ->setCvvResponseCode(@$data['CVVResponseCode'])
                ->setTransactionId(@$data['TransactionID'])
                ->setApprovalNumber(@$data['ApprovalNumber'])
                ->setApprovedAmount(@$data['ApprovedAmount'])
                ->setCardLogo(@$data['CardLogo'])
                ->save();
            $log='';
            if ($elements->successfulHostedResponse()) {
                $getAccountDetails = true;
                if ($this->scopeConfig->isSetFlag('payment/elements/CVVEnabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) &&
                    $elements->validateAvsResponse() &&
                    $elements->validateCvvResponse()) {
                    $elements->paymentAccountCreateFromTransactionId();
                    $getAccountDetails = $elements->getPaymentAccountId() != null;
                } elseif (!$this->scopeConfig->isSetFlag('payment/elements/CVVEnabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) &&
                    $this->scopeConfig->isSetFlag('payment/elements/AVSEnabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                    $elements->creditCardAvsOnly();
                    if (!$elements->validateAvsResponse()) {
                        $elements->setError($elements->getError());
                    }
                    $log ="Please enable CVV Enabled";
                }
                if ($getAccountDetails) {
                    $elements->paymentAccountQuery();
                    if ($elements->hasCardExpired()) {
                        $elements->paymentAccountDelete();
                    }
                }
            }

            if ($elements->hasError()) {
                $this->logger->error("TransactionSetupID".$elements->getTransactionSetupId()."Error". $elements->getTransactionSetupId().$log);
            }

            $this->registry->register('elements_transaction', $elements);
        } else {
            $this->logger->error(
                'TransactionSetupID:'.@$data['TransactionSetupID'].'
                not found in elements transactions record.'
            );

        }//end if


        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }


    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        $quote = $this->onepage->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount($quote->getIsMultiShipping())) {
            return true;
        }
        $action = $this->getRequest()->getActionName();

        if ($this->checkoutSession->getCartWasUpdated(true)
            &&
            !in_array($action, ['index', 'progress'])
        ) {
            return true;
    }
    return false;
}  

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    protected function _ajaxRedirectResponse()
    {
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setStatusHeader(403, '1.1', 'Session Expired')
        ->setHeader('Login-Required', 'true');
        return $resultRaw;
    }    
    

}
