<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Cre\Controller\Cards;

use \Magento\Framework\Exception\LocalizedException;

class Opcsavereview extends \Magento\Framework\App\Action\Action
{
    
    protected $resultPageFactory;
    
    protected $jsonHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    
    
    protected $tokenFactory;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory, 
                                \Magento\Framework\DataObjectFactory $dataObjectFactory, 
                                \Epicor\Cre\Model\TokenFactory $tokenFactory,
                                \Epicor\Cre\Logger\Logger $logger,
                                \Magento\Checkout\Model\Session $checkoutSession, 
                                \Magento\Framework\Json\Helper\Data $jsonHelper)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->checkoutSession   = $checkoutSession;
        $this->tokenFactory      = $tokenFactory;
        $this->jsonHelper        = $jsonHelper;
        $this->logger            = $logger;
        parent::__construct($context);
    }
    
    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $paymentMethod  = $this->getRequest()->getPostValue();
            $decodeJson     = json_decode($paymentMethod['payment'], true);
            $param          = array(
                'cre'
            );
            $paymentDetails = $this->dataObjectFactory->create($param);
            //$decodeJson = json_decode($paymentMethod);
            $paymentDetails->setData($decodeJson);
            $cre_payment_method = $this->tokenFactory->create();
            $quote              = $this->checkoutSession->getQuote();
            $return_data        = array(
                'tokenSuccess' => false
            );
            $token              = $cre_payment_method->requestToken($paymentDetails);
            if ($token) {
                $return_data['tokenSuccess'] = true;
            } else {
                $return_data['errorMsg']  = "Error";
                $return_data['errorStep'] = 'Cre Token Request';
            }
            return $this->jsonResponse($return_data);
        }
        catch (\LocalizedException $e) {
            $this->logger->info(\Psr\Log\LogLevel::DEBUG, $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($response));
    }
}