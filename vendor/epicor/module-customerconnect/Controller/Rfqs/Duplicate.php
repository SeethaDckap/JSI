<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rfqs;

class Duplicate extends \Epicor\Customerconnect\Controller\Rfqs
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_rfqs_create';

    /**
     * @var \Epicor\Customerconnect\Helper\Rfq
     */
    protected $customerconnectRfqHelper;

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
        \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper
    )
    {
        $this->customerconnectRfqHelper = $customerconnectRfqHelper;
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
        if ($this->_loadRfq()) {
            $origRfq = $this->registry->registry('customer_connect_rfq_details');
            $this->registry->unregister('customer_connect_rfq_details');
            $this->registry->unregister('rfqs_editable');

            $newRfq = $this->_initNewRfq();

            $helper = $this->customerconnectRfqHelper;

            $errors = $helper->duplicateLines($origRfq, $newRfq);
            $this->registry->register('customer_connect_rfq_details', $newRfq);

            if ($errors) {
                foreach ($errors as $productCode) {
                    //M1 > M2 Translation Begin (Rule 55)
                    //$this->generic->addError($this->__('Product %s Could not be duplicated, not currently available', $productCode));
                    $this->messageManager->addErrorMessage(__('Product %1 Could not be duplicated, not currently available', $productCode));
                    //M1 > M2 Translation End
                }
            }
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Customer Connect RFQ Duplicate'));
            $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
                $pageMainTitle->setPageTitle(__('New Quote'));
            }
            return $resultPage;
        } else {
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('*/*/index');
            }
        }
    }

}
