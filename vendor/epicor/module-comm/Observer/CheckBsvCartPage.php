<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class CheckBsvCartPage extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\Comm\Helper\Cart\SendbsvFactory
     */
    protected $sendBsvHelperFactory;
    
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\DataFactory $commHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Session\GenericFactory $generic,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\UrlInterface $urlInterface,
        \Epicor\Comm\Helper\ProductFactory $commProductHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Config\Model\Config\Source\Yesno $configConfigSourceYesno,
        \Epicor\Comm\Model\Config\Source\Customertypes $commConfigSourceCustomertypes,
        \Epicor\Lists\Model\ResourceModel\ListModel\Website\CollectionFactory $listsResourceListModelWebsiteCollectionFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Store\Group\CollectionFactory $listsResourceListModelStoreGroupCollectionFactory,
        \Epicor\Lists\Model\Config\Source\Lists $listsConfigSourceLists,
        \Epicor\Common\Helper\DataFactory $commonHelper,
        \Epicor\Comm\Model\Message\Request\AstFactory $commMessageRequestAstFactory,
        \Magento\Checkout\Model\CartFactory $checkoutCart,
        \Epicor\Comm\Helper\ConfiguratorFactory $commConfiguratorHelper,
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Epicor\Comm\Model\Message\Request\GorFactory $commMessageRequestGorFactory,
        \Epicor\Comm\Model\Message\Request\BsvFactory $commMessageRequestBsvFactory,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Payment\CollectionFactory $commResourceErpMappingPaymentCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Sales\Model\Order\PaymentFactory $salesOrderPayment,
        \Epicor\Comm\Model\Erp\Mapping\PaymentFactory $commErpMappingPayment,
        \Magento\CatalogRule\Model\RuleFactory $catalogRuleRuleFactory,
        \Epicor\Comm\Helper\Messaging\CustomerFactory $commMessagingCustomerHelper,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory,
        \Epicor\Comm\Model\Message\Request\GqrFactory $commMessageRequestGqrFactory,
        \Epicor\Comm\Helper\Product\Image\SyncFactory $commProductImageSyncHelper,
        \Psr\Log\LoggerInterface $logger,
        \Epicor\Lists\Model\ListModel\WebsiteFactory $listsListModelWebsiteFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Epicor\Lists\Model\ListModel\Store\GroupFactory $listsListModelStoreGroupFactory,
        \Epicor\Comm\Model\Message\Request\SynFactory $commMessageRequestSynFactory,
        \Magento\Backend\Model\SessionFactory $backendSession,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Country\CollectionFactory $commResourceErpMappingCountryCollectionFactory,
        \Epicor\Comm\Helper\EntityregFactory $commEntityregHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Wishlist\Model\ItemFactory $wishlistItemFactory,
        \Epicor\Comm\Helper\ReturnsFactory $commReturnsHelper,
        \Magento\Directory\Helper\DataFactory $directoryHelper,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $eavEntityAttributeSetFactory,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\ImageFactory $imageFactory,
        \Magento\Quote\Model\Quote\AddressFactory $quoteQuoteAddressFactory,
        \Epicor\Comm\Block\Adminhtml\Catalog\Category\Tab\Render\VisibilityFactory $commAdminhtmlCatalogCategoryTabRenderVisibilityFactory,
        \Epicor\Comm\Block\Adminhtml\Catalog\Category\Tab\Render\StatusFactory $commAdminhtmlCatalogCategoryTabRenderStatusFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Epicor\Common\Model\Url $url,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Epicor\Comm\Helper\Cart\SendbsvFactory $sendBsvHelperFactory
    )
    {
        parent::__construct(
            $scopeConfig,
            $commHelper,
            $storeManager,
            $frameworkHelperDataHelper,
            $generic,
            $request,
            $urlInterface,
            $commProductHelper,
            $registry,
            $configConfigSourceYesno,
            $commConfigSourceCustomertypes,
            $listsResourceListModelWebsiteCollectionFactory,
            $listsResourceListModelStoreGroupCollectionFactory,
            $listsConfigSourceLists,
            $commonHelper,
            $commMessageRequestAstFactory,
            $checkoutCart,
            $commConfiguratorHelper,
            $checkoutSession,
            $customerSessionFactory,
            $commMessageRequestGorFactory,
            $commMessageRequestBsvFactory,
            $commResourceErpMappingPaymentCollectionFactory,
            $customerCustomerFactory,
            $salesOrderPayment,
            $commErpMappingPayment,
            $catalogRuleRuleFactory,
            $commMessagingCustomerHelper,
            $customerAddressFactory,
            $commMessageRequestGqrFactory,
            $commProductImageSyncHelper,
            $logger,
            $listsListModelWebsiteFactory,
            $commResourceCustomerErpaccountCollectionFactory,
            $listsListModelStoreGroupFactory,
            $commMessageRequestSynFactory,
            $backendSession,
            $salesOrderFactory,
            $commResourceErpMappingCountryCollectionFactory,
            $commEntityregHelper,
            $eventManager,
            $catalogProductFactory,
            $wishlistItemFactory,
            $commReturnsHelper,
            $directoryHelper,
            $eavEntityAttributeSetFactory,
            $commCustomerErpaccountFactory,
            $dataObjectFactory,
            $uploaderFactory,
            $imageFactory,
            $quoteQuoteAddressFactory,
            $commAdminhtmlCatalogCategoryTabRenderVisibilityFactory,
            $commAdminhtmlCatalogCategoryTabRenderStatusFactory,
            $response,
            $urlBuilder,
            $messageManager,
            $url,
            $directoryList
        );
        
        $this->sendBsvHelperFactory = $sendBsvHelperFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        $cartContainsNonErp = $this->commHelper->create()->cartContainsNonErpProducts();
        if ($cartContainsNonErp) {
            $text = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/cart_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $this->messageManager->addNoticeMessage($text);
        }
        
        
        $bsvForCart = $this->scopeConfig->getValue('epicor_comm_enabled_messages/bsv_request/bsv_for_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $bsvTriggerForCart = $this->scopeConfig->getValue('epicor_comm_enabled_messages/bsv_request/bsv_trigger_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($bsvForCart && !$bsvTriggerForCart) {
            $quote = $this->checkoutSession->create()->getQuote();
            /* @var $quote \Epicor\Comm\Model\Quote */        
            /* @var $helper \Epicor\Comm\Helper\Cart\Sendbsv */
            $helper = $this->sendBsvHelperFactory->create();
            $sendBsv = true;
            //if non erp product check is in use and option is 'request' and if cart contains non erp products don't send bsv 
            if ($cartContainsNonErp) {
                $sendBsv = false;
            }
            if ($sendBsv && !$this->checkoutSession->create()->getData('bsv_sent_for_cart_page')) {
                $this->checkoutSession->create()->setData('bsv_sent_for_cart_page', true);
                $helper->sendCartBsv($quote, true);
            }
            
        }
    }

}