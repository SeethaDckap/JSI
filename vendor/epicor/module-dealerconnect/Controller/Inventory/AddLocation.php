<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Inventory;

use Magento\Framework\Controller\ResultFactory;

class AddLocation extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Epicor\Dealerconnect\Model\Message\Request\Deiu
     */
    protected $dealerconnectMessageRequestDeiu;
    
    
    protected $resultPageFactory;

    protected $_successMsg;
    protected $_errorMsg;    
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;    
    
    
    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;    
    
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;    

    protected $resultRedirect;
    
    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;    

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Dealerconnect\Model\Message\Request\Deiu $dealerconnectMessageRequestDeiu,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->dealerconnectMessageRequestDeiu = $dealerconnectMessageRequestDeiu;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->_localeResolver = $localeResolver;
        parent::__construct(
            $context
        );
    }

    /**
     * Index action
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        $error = false;
        $form_data = array();
        if ($data) {
            $message = $this->dealerconnectMessageRequestDeiu;
            if(isset($data['loc'])) {
                $form_data['locationAddress'] = $data['loc'];
                if(($data['actionModes'] =="update") && (isset($data['locationAddress']))) {
                    $message->addlocationAddress($form_data);
                }
                if($data['actionModes'] =="add") {
                    $message->addlocationAddress($form_data);
                }                
            }
            if(isset($data['sold'])) {
                $soldToAddress                = $data['sold'];
                if( $data['actionModes'] !="update") {
                    $message->addsoldToAddress($soldToAddress);
                }                
            }
            if(isset($data['own'])) {
                $ownerAddress                 = $data['own'];
                 if(($data['actionModes'] =="update") && (isset($data['ownerAddress']))) {
                     $message->addownerAddress($ownerAddress);
                 }
                 if($data['actionModes'] =="add") {
                    $message->addownerAddress($ownerAddress);
                 }                   
            }
            $basicInfo = $data['deiubasicjson'];
            if(!empty($basicInfo)) {
                $jsonDecode = json_decode($basicInfo,true);
                $jsonDecode['actionMode'] = $data['actionModes'];
                $jsonDecode['tranComment']  = $data['transComment'];
            }
            
            
            
            if(isset($data['warrantyClaim']) && $data['warrantyClaim'] =="on" && isset($data['warranty']['code']) && $data['warranty']['code']) {
                $warranty     = $data['warranty'];
                $jsonDecode['warranty_code'] = $warranty['code'];
                $jsonDecode['warranty_start_date'] = $this->customerconnectMessagingHelper->getFormattedInputDate($warranty['start'], 'yyyy-MM-ddTHH:mm:ssZ');
                $jsonDecode['warranty_expiration_date'] = $this->customerconnectMessagingHelper->getFormattedInputDate($warranty['expiry'], 'yyyy-MM-ddTHH:mm:ssZ');
                $jsonDecode['warranty_comment'] = $warranty['comment'];
            }            
            $message->addBasicInformation($jsonDecode);
            //$message->setAddressType('delivery');
            $resultData = $this->sendUpdate($message);

        } else {
            $error = true;
        }
        if ($error) {
            $resultData = array('redirect' => $this->_url->getUrl('dealerconnect/inventory/'), 'type' => 'success');
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
    
    
    
    public  function sendUpdate($message) {
        $helper = $this->customerconnectHelper;
        $erp_account_number = $helper->getErpAccountNumber();
        $messageTypeCheck = $message->getHelper()->getMessageType('DEIU');
        if ($message->isActive() && $messageTypeCheck) {
            //M1 > M2 Translation Begin (Rule p2-6.4)
            /*$message->setAccountNumber($erp_account_number)
                ->setLanguageCode($helper->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()));*/
            $message->setAccountNumber($erp_account_number)
                     ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
            //M1 > M2 Translation End
            $this->_errorMsg = __('Failed to update Inventory Information');
            if ($message->sendMessage()) {
                $this->_successMsg = __('Inventory Information updated successfully');
                $this->messageManager->addSuccessMessage($this->_successMsg);
            } else {
                $this->messageManager->addErrorMessage($this->_errorMsg . ': ' . $message->getStatusDescription());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Inventory Information update not available'));
        }
        return array('redirect' => $this->_url->getUrl('dealerconnect/inventory/'), 'type' => 'success');
    }

}
