<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Inventory;

class BillOfMaterials extends \Epicor\AccessRight\Controller\Action
{

    const FRONTEND_RESOURCE = 'Dealer_Connect::dealer_inventory_details';
    /**
     * @var \Epicor\Dealerconnect\Model\Message\Request\Cuos
     */
    protected $dealerconnectMessageRequestDebm;
    
    
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
    
    /**
     *
     * @var \Magento\Customer\Model\Session 
     */
    protected $customerSession;
    
    /**
     *
     * @var \Magento\Framework\View\Result\LayoutFactory 
     */
    protected $resultLayoutFactory;

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
        \Epicor\Dealerconnect\Model\Message\Request\Debm $dealerconnectMessageRequestDebm
    )
    {
        $this->_localeResolver = $localeResolver;
        $this->dealerconnectMessageRequestDebm = $dealerconnectMessageRequestDebm;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->request = $request;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->urlDecoder = $urlDecoder;
        $this->encryptor = $encryptor;   
        $this->customerSession = $customerSession;
        parent::__construct(
            $context
        );
    }
    /**
     * Index action
     */
    public function execute()
    {
        $details = $this->_getBomDetails();
        $debm = $this->dealerconnectMessageRequestDebm;
        $helper = $this->customerconnectHelper;
        $messageTypeCheck = $debm->getHelper()->getMessageType('DEBM');
        if ($debm->isActive() && $messageTypeCheck && isset($details)) {
            $resultPage = $this->resultPageFactory->create();
            $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
            if ($block) {
                $refererUrl = $this->_redirect->getRefererUrl();
                if (strpos($refererUrl, 'dealerconnect/inventory/billOfMaterials') === false) {
                    $this->customerSession->unsBomBackUrl();
                    $this->customerSession->setBomBackUrl($refererUrl);
                } else {
                    $refererUrl = $this->customerSession->getBomBackUrl();
                }
                $block->setRefererUrl($refererUrl);
            }
            $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
            $debmData = $this->registry->registry('debm_details');
            $page_title = 'Bill Of Materials';
            if(isset($details[2]) && $details[2]!= ''){
                $page_title = $page_title.'- Identification Number: '.$details[2];
            }
            $pageMainTitle->setPageTitle(__($page_title));

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
    protected function _getBomDetails()
    {
        $results = false;
        $locationInfo = $this->request->getParam('location');
        $helper = $this->customerconnectHelper;
        $erpAccountNumber = $helper->getErpAccountNumber();
        $locationDetails = explode(']:[', $this->encryptor->decrypt($this->urlDecoder->decode($locationInfo)));
        if (
            count($locationDetails) > 2 &&
            $locationDetails[0] == $erpAccountNumber &&
            !empty($locationDetails[1])
        ) {
            $debm = $this->dealerconnectMessageRequestDebm;
            $debm->setAccountNumber($locationDetails['0'])
                 ->setLocationNumber($locationDetails['1'])
                 ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
            if ($debm->sendMessage()) {            
                $debmData = $debm->getResults();
                $debmTrans = $debm->getTransResults();
                $this->registry->register('debm_details', $debmData);
                $this->registry->register('debm_trans_details', $debmTrans);
            }            
            $results = $locationDetails;
        } else {
            $results = false;
        }
        return $results;
    }    

}
