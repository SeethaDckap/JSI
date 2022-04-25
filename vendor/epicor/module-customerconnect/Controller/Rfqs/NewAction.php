<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rfqs;

class NewAction extends \Epicor\Customerconnect\Controller\Rfqs
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_rfqs_create';

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
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Model\Message\Request\Crqd $customerconnectMessageRequestCrqd,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
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
        \Epicor\Comm\Helper\Data $commHelper
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
            $customerconnectHelper,
            $request,
            $customerconnectMessageRequestCrqd,
            $generic,
            $commonAccessHelper,
            $customerconnectMessagingHelper,
            $commMessagingHelper,
            $commConfiguratorHelper,
            $commProductHelper,
            $catalogProductFactory,
            $storeManager,
            $commMessageRequestCdmFactory,
            $scopeConfig,
            $commonXmlvarienFactory,
            $urlDecoder,
            $encryptor
        );
    }

    public function execute()
    {
        $rfq = $this->_initNewRfq();
        $this->registry->register('customer_connect_rfq_details', $rfq);

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
            $pageMainTitle->setPageTitle(__('New Quote'));
        }
        $resultPage->getConfig()->getTitle()->set(__('Customer Connect NEW RFQ'));
        return $resultPage;
    }

}
