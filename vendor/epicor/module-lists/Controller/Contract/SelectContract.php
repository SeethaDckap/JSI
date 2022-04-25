<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Contract;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;

class SelectContract extends \Epicor\Lists\Controller\Contract
{


    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;
    
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    )
    {
        $this->_storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_cookieManager = $cookieManager;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $listsFrontendContractHelper,
            $listsListModelFactory
        );
    }
    
    /**
     * Contract Select Action
     */
    public function execute() {
        $contract = $this->getRequest()->getParam('contract');
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */

        if ($contract && $helper->isValidContractId($contract)) {
            $helper->selectContract($contract);
        }

        if ($contract == -1) {
            $helper->selectContract(null);
            // clear the contract code from the quote, as no contract is selected
            $quote = $this->checkoutSession->getQuote();
            $quote->setEccContractCode(null);
            $quote->save();
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $returnUrl = $this->getRequest()->getParam('return_url');
        
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath('/');
        $sectiondata = json_decode($this->_cookieManager->getCookie('section_data_ids'));
        $sectiondata->cart += 1000;
        $this->_cookieManager->setPublicCookie(
            'section_data_ids',
            json_encode($sectiondata),
            $metadata
        );
        
        if ($returnUrl) {
            $returnUrl = $helper->urlDecode($returnUrl);
           $resultRedirect->setUrl($returnUrl);
            
        } else {
            $url = $this->_storeManager->getStore()->getBaseUrl();
            
           $resultRedirect->setUrl($url);
            //$this->_redirect('*/*/');
        }
        return $resultRedirect;
    }

}
