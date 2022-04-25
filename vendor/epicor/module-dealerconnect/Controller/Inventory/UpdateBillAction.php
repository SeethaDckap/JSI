<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Inventory;

use Magento\Framework\Controller\ResultFactory;

class UpdateBillAction extends \Magento\Framework\App\Action\Action {

    /**
     * @var \Epicor\Dealerconnect\Model\Message\Request\Deiu
     */
    protected $dealerconnectMessageRequestDmau;
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
    \Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Locale\ResolverInterface $localeResolver, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, \Magento\Framework\App\Request\Http $request, \Epicor\Customerconnect\Helper\Data $customerconnectHelper, \Magento\Framework\Registry $registry, \Magento\Framework\Session\Generic $generic, \Magento\Framework\Url\Decoder $urlDecoder, \Magento\Framework\Encryption\EncryptorInterface $encryptor, \Epicor\Dealerconnect\Model\Message\Request\Dmau $dealerconnectMessageRequestDmau, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->dealerconnectMessageRequestDmau = $dealerconnectMessageRequestDmau;
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
        $formData = [];
        $data = $this->getRequest()->getPost();
        parse_str($data['formdata'], $formData);
        $customPart = isset($formData['custom_part']) ? 1 : 0;
        if (!empty($data['productcode']) && !empty($formData['old']['product_code'])) {
            $mode = 'N';
        } else if (empty($formData['old']['product_code']) && !empty($data['productcode'])) {
            $mode = 'A';
        }
        $error = false;

        if ($data) {
            $message = $this->dealerconnectMessageRequestDmau;
            $basicDetails['location_number'] = $data['locNum'];
            $basicDetails['identification_number'] = $data['IdNum'];
            $basicDetails['serial_number'] = isset($data['SerNum']) ? $data['SerNum'] : '';
            $message->addBasicInformation($basicDetails);
            if($mode === 'A'){
                $message->materialsAddition($formData['new'], $mode, $data['productcode'], $data['description'], $customPart);
            }else{
                $message->addMaterials($formData['old'], $mode, $customPart);
                $message->addMaterialsReplaced($formData['new'], $mode, $data['productcode'], $data['description'], $customPart);
            }
            $resultData = $this->sendUpdate($message);
        } else {
            $this->error = true;
        }

        if ($this->error) {
            $resultData = array('redirect' => $this->_url->getCurrentUrl(), 'type' => 'err');
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultRedirect->setData($resultData);
        return $resultRedirect;
    }

    public function sendUpdate($message) 
    {
        $helper = $this->customerconnectHelper;
        $erp_account_number = $helper->getErpAccountNumber();
        $messageTypeCheck = $message->getHelper()->getMessageType('DMAU');
        $redirectUrl = $this->_url->getCurrentUrl();
        if ($message->isActive() && $messageTypeCheck) {
            $this->_errorMsg = __('Failed to update Bill of materials');
            if ($message->sendMessage()) {
                $this->_successMsg = __('Bill of materials updated successfully');
                $this->messageManager->addSuccessMessage($this->_successMsg);
            } else {
                $this->error = true;
                $redirectUrl = $this->_url->getCurrentUrl();
                $this->messageManager->addErrorMessage($this->_errorMsg . ': ' . $message->getStatusDescription());
            }
        } else {
            $this->error = true;
            $redirectUrl = $this->_url->getCurrentUrl();
            $this->messageManager->addErrorMessage(__('Bill of materials update not available'));
        }
        return array('redirect' => $redirectUrl, 'type' => 'success');
    }

}
