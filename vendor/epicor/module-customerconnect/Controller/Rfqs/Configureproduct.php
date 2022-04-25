<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rfqs;

class Configureproduct extends \Epicor\Customerconnect\Controller\Rfqs {

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProductHelper;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Locale\ResolverInterface $localeResolver, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, \Magento\Framework\Registry $registry, \Epicor\Customerconnect\Helper\Data $customerconnectHelper, \Magento\Framework\App\Request\Http $request, \Epicor\Customerconnect\Model\Message\Request\Crqd $customerconnectMessageRequestCrqd, \Magento\Framework\Session\Generic $generic, \Epicor\Common\Helper\Access $commonAccessHelper, \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper, \Epicor\Comm\Helper\Messaging $commMessagingHelper, \Epicor\Comm\Helper\Configurator $commConfiguratorHelper, \Epicor\Comm\Helper\Product $commProductHelper, \Magento\Catalog\Model\ProductFactory $catalogProductFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Epicor\Comm\Model\Message\Request\CdmFactory $commMessageRequestCdmFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory, \Magento\Framework\Url\DecoderInterface $urlDecoder, \Magento\Framework\Encryption\EncryptorInterface $encryptor, \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper, \Epicor\Common\Helper\File $commonFileHelper, \Epicor\Comm\Helper\Data $commHelper, \Epicor\Customerconnect\Model\Message\Request\Crqu $customerconnectMessageRequestCrqu, \Epicor\SalesRep\Helper\Pricing\Rule\Product $salesRepPricingRuleProductHelper, \Magento\Framework\Url\EncoderInterface $urlEncoder, \Magento\Framework\Json\Helper\Data $jsonHelper,\Magento\Catalog\Helper\Product $catalogProductHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->catalogProductHelper = $catalogProductHelper;
        parent::__construct(
                $context, $customerSession, $localeResolver, $resultPageFactory, $resultLayoutFactory, $registry, $customerconnectHelper, $request, $customerconnectMessageRequestCrqd, $generic, $commonAccessHelper, $customerconnectMessagingHelper, $commMessagingHelper, $commConfiguratorHelper, $commProductHelper, $catalogProductFactory, $storeManager, $commMessageRequestCdmFactory, $scopeConfig, $commonXmlvarienFactory, $urlDecoder, $encryptor
        );
    }

    public function execute() {
        $productId = $this->request->getParam('productid');
        $child = $this->request->getParam('child');
        $options = $this->request->getParam('options');

        if ($child) {
            $childProd = $this->catalogProductFactory->create()->load($child);
            $this->registry->register('child_product', $childProd);
        }

        if ($options) {
            $options = unserialize(base64_decode($options));
            $optionsData = array();
            foreach ($options as $option) {
                $optionsData[$option['description']] = $option['value'];
            }
            $this->registry->register('options_data', $optionsData);
        }

        $product = $this->catalogProductHelper->initProduct($productId, $this);
        /* @var $product Mage_Core_Model_Generic */

        $helper = $this->commProductHelper;
        /* @var $helper Epicor_Comm_Helper_Product */
        $result = $this->resultLayoutFactory->create();
        $block = $result->getLayout()->createBlock('Epicor\Comm\Block\Catalog\Product\Configure');
        /* @var $block Epicor_Comm_Block_Catalog_Product_Configure */
        //echo $block->toHtml();die('kbye');
        $response = array(
            'error' => false,
            'html' => $block->toHtml(),
            //M1 > M2 Translation Begin (Rule p2-7)
            //'jsonconfig' => Mage::helper('core')->jsonDecode($block->getJsonConfig())
            'jsonconfig' => $this->jsonHelper->jsonDecode($block->getJsonConfig())
                //M1 > M2 Translation End
        );

        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($response));
    }

}
