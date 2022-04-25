<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rfqs;

class Confirm extends \Epicor\Customerconnect\Controller\Rfqs {

    /**
     * @var \Epicor\Customerconnect\Helper\Rfq
     */
    protected $customerconnectRfqHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
    \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper, \Epicor\Comm\Helper\Data $commHelper, \Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Locale\ResolverInterface $localeResolver, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, \Magento\Framework\Registry $registry, \Epicor\Customerconnect\Helper\Data $customerconnectHelper, \Magento\Framework\App\Request\Http $request, \Epicor\Customerconnect\Model\Message\Request\Crqd $customerconnectMessageRequestCrqd, \Magento\Framework\Session\Generic $generic, \Epicor\Common\Helper\Access $commonAccessHelper, \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper, \Epicor\Comm\Helper\Messaging $commMessagingHelper, \Epicor\Comm\Helper\Configurator $commConfiguratorHelper, \Epicor\Comm\Helper\Product $commProductHelper, \Magento\Catalog\Model\ProductFactory $catalogProductFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Epicor\Comm\Model\Message\Request\CdmFactory $commMessageRequestCdmFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory, \Magento\Framework\Url\DecoderInterface $urlDecoder, \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->customerconnectRfqHelper = $customerconnectRfqHelper;
        $this->commHelper = $commHelper;
        parent::__construct(
                $context, $customerSession, $localeResolver, $resultPageFactory, $resultLayoutFactory, $registry, $customerconnectHelper, $request, $customerconnectMessageRequestCrqd, $generic, $commonAccessHelper, $customerconnectMessagingHelper, $commMessagingHelper, $commConfiguratorHelper, $commProductHelper, $catalogProductFactory, $storeManager, $commMessageRequestCdmFactory, $scopeConfig, $commonXmlvarienFactory, $urlDecoder, $encryptor
        );
    }

    public function execute() {
        $data = $this->getRequest()->getPost();

        $response = json_encode(array('message' => __('No Data Sent'), 'type' => 'error'));

        if ($data) {
            $helper = $this->customerconnectRfqHelper;
            /* @var $helper Epicor_Customerconnect_Helper_Rfq */

            $commHelper = $this->commHelper;
            /* @var $commHelper Epicor_Comm_Helper_Data */

            $data = $commHelper->sanitizeData($data);

            $response = $helper->processRfqCrqc('confirm', $data, $response);
        }

        $this->getResponse()->setBody($response);
    }

}
