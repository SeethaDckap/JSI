<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rfqs;

class Index extends \Epicor\Customerconnect\Controller\Rfqs
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_rfqs_read';

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
        \Epicor\Customerconnect\Model\Message\Request\Crqs $customerconnectMessageRequestCrqs,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
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

    /**
     * Index action
     */
    public function execute()
    {
        $crqs = $this->customerconnectMessageRequestCrqs;
        $messageTypeCheck = $crqs->getHelper()->getMessageType('CRQS');

        if ($crqs->isActive() && $messageTypeCheck) {
            $accessHelper = $this->commonAccessHelper;
            $access = $accessHelper->customerHasAccess('Epicor_Customerconnect', 'Rfqs', 'confirmreject', '', 'Access');
            $this->registry->register('rfqs_editable', $access);

            return $this->resultPageFactory->create();
        } else {
            $this->messageManager->addErrorMessage('ERROR - RFQ Search not available');
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

}
