<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Esdm\Controller\Index;
use \Magento\Framework\Exception\LocalizedException;

class Opcsavereview extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;    

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */


    /**
     * @var \Epicor\Esdm\Model\TokenFactory
     */
    protected $esdmFactory;



    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $checkoutSession;    

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Esdm\Model\Payment\EsdmFactory $esdmFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->esdmTokenFactory  = $esdmFactory;
        $this->jsonHelper = $jsonHelper;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }


    public function _construct()
    {
        $this->_init('Epicor\Esdm\Model\ResourceModel\Token');
    }    

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $paymentMethod = $this->getRequest()->getPostValue();
           // $arrayParam  = array('payment'=>$paymentMethod);

            $param = array('esdm');
            $paymentDetails = $this->dataObjectFactory->create($param);
            $paymentDetails->setData($paymentMethod);

            $esdm_payment_method = $this->esdmTokenFactory->create();
            $quote = $this->checkoutSession->getQuote();
            /* @var $esdm_payment_method Epicor_Esdm_Model_Token */
            //$ref = $quote->getCustomerEmail() . ' : Quote ' . $quote->getId();
            $return_data = array(
                'tokenSuccess' => false,
                'logoUrl' => ''
            );
            $token = $esdm_payment_method->requestToken($paymentDetails, $paymentDetails->getEsdmTokenId());
            $return_data['tokenSuccess'] = $token->getSuccess();
            $return_data['debug']['tokenInfo'] = $token->debug();
            if (!$token->getSuccess()) {
                $return_data['errorMsg'] = $token->getErrormsg();
                $return_data['errorStep'] = 'ESDM Token Request';
            } else {
                if (isset($paymentMethod['esdm_token_id']) && $paymentMethod['esdm_token_id'] != '') {
                    $quote->getPayment()->setEccIsSaved(1);
                }
                $quote->getPayment()->setEccCcvToken($token->getCcvToken());
                $quote->getPayment()->setEccCvvToken($token->getCvvToken());
                $quote->getPayment()->save();
            }
            $return_data['debug']['grandTotal'] = $quote->getGrandTotal();
            $return_data['debug']['esdm'] = var_export($esdm_payment_method->getEsdm(), true);
            $return_data['debug']['payment'] = $paymentDetails;
            $return_data['debug']['quote'] = $quote->debug();  
            return $this->jsonResponse($return_data);
        } catch (\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}
