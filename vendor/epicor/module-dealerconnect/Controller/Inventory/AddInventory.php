<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Inventory;

use Magento\Framework\Controller\ResultFactory;

class AddInventory extends \Magento\Framework\App\Action\Action
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

    protected $error;
    
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
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->dealerconnectMessageRequestDeiu = $dealerconnectMessageRequestDeiu;
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
        $form_data = $basicDetails = $ownerAddress = $soldToAddress = [];
        $helper = $this->customerconnectHelper;
        $erp_account_number = $helper->getErpAccountNumber();
        $delimiter = $helper->getUOMSeparator();
        $parts = explode($delimiter, $erp_account_number, 2);
        $erp_account_number = $parts[count($parts) - 1];
        if ($data) {
            $message = $this->dealerconnectMessageRequestDeiu;
            if(isset($data['sold'])) {
                $soldToAddress = $data['sold'];
                $soldToAddress['account_number'] = $erp_account_number;
                $message->addsoldToAddress($soldToAddress);
            }
            
            if (isset($data['copySoldAddress']) && isset($data['sold'])) {
                $ownerAddress = $soldToAddress;
                $message->addownerAddress($ownerAddress);
            } else if(isset($data['own'])) {
                $ownerAddress = $data['own'];
                $ownerAddress['account_number'] = $erp_account_number;
                $message->addownerAddress($ownerAddress);
            }
            
            if (isset($data['copyOwnAddress'])) {
                $form_data['locationAddress'] = $ownerAddress;
                $message->addlocationAddress($form_data);
            } else if(isset($data['loc'])) {
                $form_data['locationAddress'] = $data['loc'];
                $form_data['locationAddress']['account_number'] = $erp_account_number;
                $message->addlocationAddress($form_data);
            }
            
            
            
            $basicDetails['identification_number'] = $data['identification_number'];
            $basicDetails['serial_number'] = $data['serial_number'];
            $basicDetails['product_code'] = $data['product_code'];
            $basicDetails['description'] = $data['description'];
            $basicDetails['actionMode'] = "add";
            $basicDetails['location_number'] = "";
            $basicDetails['tranComment'] = "";
            $basicDetails['listing'] = "";
            $basicDetails['listing_date'] = "";
            $basicDetails['warranty_code'] = "";
            $basicDetails['warranty_comment'] = "";
            $basicDetails['warranty_expiration_date'] = "";
            $basicDetails['warranty_start_date'] = "";
            
            if(isset($data['warrantyClaim']) && $data['warrantyClaim'] =="on" && isset($data['warranty']['code']) && $data['warranty']['code']) {
                $warranty = $data['warranty'];
                $basicDetails['warranty_code'] = $warranty['code'];
                $basicDetails['warranty_start_date'] = $helper->getFormattedInputDate($warranty['start'], 'yyyy-MM-ddTHH:mm:ssZ');
                $basicDetails['warranty_expiration_date'] = $helper->getFormattedInputDate($warranty['expiry'], 'yyyy-MM-ddTHH:mm:ssZ');
                $basicDetails['warranty_comment'] = $warranty['comment'];
            }            
            $message->addBasicInformation($basicDetails);
            //$message->setAddressType('delivery');
            $resultData = $this->sendUpdate($message);

        } else {
            $this->error = true;
        }
        
        if ($this->error) {
            $resultData = array('redirect' => $this->_url->getUrl('dealerconnect/inventory/new'), 'type' => 'err');
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirectUrl = isset($resultData['redirect']) ? $resultData['redirect'] : $this->_url->getUrl('dealerconnect/inventory/search');
        
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
    
    
    
    public  function sendUpdate($message) {
        $helper = $this->customerconnectHelper;
        $erp_account_number = $helper->getErpAccountNumber();
        $messageTypeCheck = $message->getHelper()->getMessageType('DEIU');
        $redirectUrl = $this->_url->getUrl('dealerconnect/inventory/search');
        if ($message->isActive() && $messageTypeCheck) {
            $message->setAccountNumber($erp_account_number)
                     ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
            $this->_errorMsg = __('Failed to Add Inventory Information');
            if ($message->sendMessage()) {
                $this->_successMsg = __('Inventory Information Added successfully');
                $this->messageManager->addSuccessMessage($this->_successMsg);
            } else {
                $this->error = true;
                $redirectUrl = $this->_url->getUrl('dealerconnect/inventory/new');
                $this->messageManager->addErrorMessage($this->_errorMsg . ': ' . $message->getStatusDescription());
            }
        } else {
            $this->error = true;
            $redirectUrl = $this->_url->getUrl('dealerconnect/inventory/new');
            $this->messageManager->addErrorMessage(__('Account update not available'));
        }
        return array('redirect' => $redirectUrl, 'type' => 'success');
    }

}
