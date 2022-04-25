<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Claims;

class Index extends \Epicor\Dealerconnect\Controller\Claims
{
    const FRONTEND_RESOURCE = 'Dealer_Connect::dealer_claim_read';
    /**
     * @var \Epicor\Dealerconnect\Model\Message\Request\Dcls
     */
    protected $dealerconnectMessageRequestDcls;

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
        \Epicor\Dealerconnect\Model\Message\Request\Dcls $dealerconnectMessageRequestDcls,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Dealerconnect\Model\Message\Request\Deid $dcMessageRequestDeid
    )
    {
        $this->dealerconnectMessageRequestDcls = $dealerconnectMessageRequestDcls;
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

    /**
     * Index action
     */
    public function execute()
    {
        $dcls = $this->dealerconnectMessageRequestDcls;
        $messageTypeCheck = $dcls->getHelper()->getMessageType('DCLS');
 
        if ($dcls->isActive() && $messageTypeCheck) {
//            $accessHelper = $this->commonAccessHelper;
//            $access = $accessHelper->customerHasAccess('Epicor_Customerconnect', 'Rfqs', 'confirmreject', '', 'Access');
//            $this->registry->register('rfqs_editable', $access);
//            echo 'I am in';
//            exit(1);
            return $this->resultPageFactory->create();
        } else {
            $this->messageManager->addErrorMessage('ERROR - Claims Search not available');
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

}
