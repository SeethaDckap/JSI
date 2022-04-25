<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Controller\Payment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Captcha\Helper\Data;

/**
 * Elements Payment  
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 */
class Opcsavereview extends SaveReview
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Epicor\Elements\Model\TransactionFactory
     */
    protected $elementsTransactionFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;    

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;    
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;    

    protected $onepage;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Data
     */
    private $helper;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Elements\Model\TransactionFactory $elementsTransactionFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Checkout\Model\Type\Onepage $onepage,
        ScopeConfigInterface $scopeConfig,
        Data $helper
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->elementsTransactionFactory = $elementsTransactionFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->jsonHelper  = $jsonHelper;
        $this->onepage = $onepage;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        parent::__construct(
            $context
        );
    }



    public function execute()
    {
        $isCaptchaEnabled = $this->scopeConfig->isSetFlag('payment/elements/enable_captcha');
        $resultJson = $this->resultJsonFactory->create();
        if ($isCaptchaEnabled) {
            $formId = 'element-payment-form';
            $captcha = $this->helper->getCaptcha($formId);
            $word = $this->getRequest()->getParam('captcha_string');
            if ($captcha->isCorrect($word) != true) {
                $return_data = [
                    'setupSuccess' => 'C',
                    'defaultMsq' => __('Incorrect Captcha'),
                    'genericError' => __('Incorrect Captcha')
                ];

                $resultJson->setData(json_encode($return_data));
                return $resultJson;
            }
        }

        $quote = $this->checkoutSession->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */

        $params = array('elements');

        $paymentDetails = $this->dataObjectFactory->create($params);
		$ref = $this->prepareRefValue($quote->getId());

        /**
         * @var \Epicor\Elements\Model\Transaction $elements
         */
        $elements = $this->elementsTransactionFactory->create();
        /* @var $elements Epicor_Elements_Model_Transaction */
        $return_data = array(
            'setupSuccess' => 'N',
            'defaultMsq' => __('Error occured while setting up Card payment.\nPlease Try again or please use another payment method'),
            'genericError' => __('Error occured while setting up Card payment.\nPlease Try again or please use another payment method')
        );

        $elements->transactionSetup($paymentDetails, $quote, $ref);
        $return_data['setupSuccess'] = $elements->getTransactionSetupExpressResponseCode() == 0 ? 'Y' : 'N';

        $return_data['debug']['tokenInfo'] = $elements->debug();
        if ($elements->getTransactionSetupExpressResponseCode() != 0) {
            $return_data['errorMsg'] = $elements->getTransactionSetupExpressResponseMessage();
            $return_data['errorStep'] = 'TransactionSetup';
        } else {
            $return_data['transactionSetupUrl'] = $elements->getTransactionSetupUrl(
                $this->getRequest()->getParam('isMobile')
            );
        }

        $return_data['debug']['grandTotal'] = $quote->getGrandTotal();
        $return_data['debug']['elements'] = var_export($elements->debug(), true);
        $return_data['debug']['payment'] = $paymentDetails;
        $return_data['debug']['quote'] = $quote->debug();
        $resultJson->setData(json_encode($return_data));
        return $resultJson;
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