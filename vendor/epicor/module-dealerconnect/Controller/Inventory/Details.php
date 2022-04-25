<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Inventory;

class Details extends \Epicor\AccessRight\Controller\Action
{
    const FRONTEND_RESOURCE = 'Dealer_Connect::dealer_inventory_details';
    /**
     * @var \Epicor\Dealerconnect\Model\Message\Request\Cuos
     */
    protected $dealerconnectMessageRequestDeid;
    
    
    protected $resultPageFactory;
    
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    protected $urlDecoder;

    protected $encryptor;


    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;    
    
    
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;    

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
        \Epicor\Dealerconnect\Model\Message\Request\Deid $dealerconnectMessageRequestDeid
    )
    {
        $this->_localeResolver = $localeResolver;
        $this->dealerconnectMessageRequestDeid = $dealerconnectMessageRequestDeid;
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->urlDecoder = $urlDecoder;
        $this->encryptor = $encryptor;        
        parent::__construct(
            $context
        );
    }

    /**
     * Index action
     */
    public function execute()
    {
        $details = $this->_getLocationDetails();
        $deid = $this->dealerconnectMessageRequestDeid;
        $helper = $this->customerconnectHelper;
        $messageTypeCheck = $deid->getHelper()->getMessageType('DEID');
        if ($deid->isActive() && $messageTypeCheck && isset($details)) {
            $resultPage = $this->resultPageFactory->create();
            $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
            $deidData = $this->registry->registry('deid_order_details');
            $pageMainTitle->setPageTitle(__('Identification Number : %1', $deidData->getIdentificationNumber()));
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__("ERROR - Inventory Details not available"));
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                return $this->resultRedirectFactory->create()->setPath('customer/account/index');
            }
        }
    }
    
    /**
     * Performs a CUOD request
     * 
     * @return boolean
     */
    protected function _getLocationDetails()
    {
        $results = false;
        $locationInfo = $this->request->getParam('location');
        $helper = $this->customerconnectHelper;
        $erpAccountNumber = $helper->getErpAccountNumber();
        $locationDetails = explode(']:[', $this->encryptor->decrypt($this->urlDecoder->decode($locationInfo)));
        if (
            count($locationDetails) == 2 &&
            $locationDetails[0] == $erpAccountNumber &&
            !empty($locationDetails[1])
        ) {
            $deid = $this->dealerconnectMessageRequestDeid;
            $deid->setAccountNumber($locationDetails['0'])
                 ->setLocationNumber($locationDetails['1'])
                 ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
            if ($deid->sendMessage()) {            
                $deidData = $deid->getResults();
                $this->registry->register('deid_order_details', $deidData);
            }            
            $results = $locationDetails;
        } else {
            $results = false;
        }
        return $results;
    }    

}
