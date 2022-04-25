<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Claims;

class Details extends \Epicor\Dealerconnect\Controller\Claims
{
    const FRONTEND_RESOURCE = 'Dealer_Connect::dealer_claim_details';
    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Crqs
     */
    protected $customerconnectMessageRequestCrqs;
    
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
            \Epicor\Dealerconnect\Model\Message\Request\Deid $dcMessageRequestDeid,
            \Epicor\Customerconnect\Model\Message\Request\Crqs $customerconnectMessageRequestCrqs
     )
    {
          $this->customerconnectMessageRequestCrqs = $customerconnectMessageRequestCrqs;
          
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
        if ($this->_loadClaim()) {
            $resultPage = $this->resultPageFactory->create();
            $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
                $claim = $this->registry->registry('dealer_connect_claim_details');
                $pageMainTitle->setPageTitle(__('Claim Number : %1', $claim->getCaseNumber()));
            }
            /* Check for Dealer Quotes claim & Rejectoption should be accessible aor not */ 
           $crqs = $this->customerconnectMessageRequestCrqs;
           $messageTypeCheck = $crqs->getHelper()->getMessageType('CRQS');

            if ($crqs->isActive() && $messageTypeCheck) {
                $accessHelper = $this->commonAccessHelper;
                $access = $accessHelper->customerHasAccess('Epicor_Customerconnect', 'Rfqs', 'confirmreject', '', 'Access');
                $this->registry->register('rfqs_editable', $access);
             }
            return $resultPage;
        }

        if ($this->messageManager->getMessages()->getItems()) {
            session_write_close();
            $this->_redirect('*/*/index');
        }
    }

}
