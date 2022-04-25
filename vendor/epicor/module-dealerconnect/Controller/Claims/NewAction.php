<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Claims;

class NewAction extends \Epicor\Dealerconnect\Controller\Claims
{
    const FRONTEND_RESOURCE = 'Dealer_Connect::dealer_claim_create';
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Helper\Messaging $dealerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Dealerconnect\Model\Message\Request\Dcld $dealerconnectMessageRequestDcld,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Model\Message\Request\CdmFactory $commMessageRequestCdmFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Dealerconnect\Model\Message\Request\Deid $dcMessageRequestDeid
    )
    {
        $this->commHelper = $commHelper;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $registry,
            $dealerconnectHelper,
            $request,
            $dealerconnectMessageRequestDcld,
            $generic,
            $commonAccessHelper,
            $commMessagingHelper,
            $commConfiguratorHelper,
            $commProductHelper,
            $catalogProductFactory,
            $storeManager,
            $commMessageRequestCdmFactory,
            $scopeConfig,
            $commonXmlvarienFactory,
            $urlDecoder,
            $encryptor,
            $dcMessageRequestDeid
        );
    }

    public function execute()
    {
        $claim = $this->_initNewClaim();
        $this->registry->register('dealer_connect_claim_details', $claim);

        $erpAccount = $this->commHelper->getErpAccountInfo();
        $currencyCode = $erpAccount->getCurrencyCode($this->storeManager->getStore()->getBaseCurrencyCode());
        
        $customer = $this->customerSession->getCustomer();
       
        if(!$currencyCode){
             $this->messageManager->addErrorMessage(__('Currency Code not found'));
              session_write_close();
              $this->_redirect('*/*/index');  
        }else{
            if ($this->messageManager->getMessages()->getItems() && !$this->customerSession->getMasqueradeAccountId()){
                  session_write_close();
                  $this->_redirect('*/*/index');  
            }
        }
        
        $resultPage = $this->resultPageFactory->create();
        $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
            $pageMainTitle->setPageTitle(__('New Claim'));
        }

        return $resultPage;
    }

}
