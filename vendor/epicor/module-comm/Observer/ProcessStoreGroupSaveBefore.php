<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ProcessStoreGroupSaveBefore extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $_gor;

    protected $_bsv;

    protected $scopeConfig;

    protected $commHelper;

    protected $storeManager;

    protected $frameworkHelperDataHelper;

    protected $generic;

    protected $request;

    protected $urlInterface;

    protected $commProductHelper;

    protected $registry;

    protected $configConfigSourceYesno;

    protected $commConfigSourceCustomertypes;

    protected $listsResourceListModelWebsiteCollectionFactory;

    protected $listsResourceListModelStoreGroupCollectionFactory;

    protected $listsConfigSourceLists;

    protected $commonHelper;

    protected $commMessageRequestAstFactory;

    protected $checkoutCart;

    protected $commConfiguratorHelper;

    protected $checkoutSession;

    protected $customerSessionFactory;

    protected $commMessageRequestGorFactory;

    protected $commMessageRequestBsvFactory;

    protected $commResourceErpMappingPaymentCollectionFactory;

    protected $customerCustomerFactory;

    protected $salesOrderPayment;

    protected $commErpMappingPayment;

    protected $catalogRuleRuleFactory;

    protected $commMessagingCustomerHelper;

    protected $customerAddressFactory;

    protected $commMessageRequestGqrFactory;

    protected $commProductImageSyncHelper;

    protected $logger;

    protected $listsListModelWebsiteFactory;

    protected $commResourceCustomerErpaccountCollectionFactory;

    protected $listsListModelStoreGroupFactory;

    protected $commMessageRequestSynFactory;

    protected $backendSession;

    protected $salesOrderFactory;

    protected $commResourceErpMappingCountryCollectionFactory;

    protected $commEntityregHelper;

    protected $eventManager;

    protected $catalogProductFactory;

    protected $wishlistItemFactory;

    protected $commReturnsHelper;

    protected $directoryHelper;

    protected $eavEntityAttributeSetFactory;

    protected $commCustomerErpaccountFactory;

    protected $dataObjectFactory;

    protected $uploaderFactory;

    protected $imageFactory;

    protected $quoteQuoteAddressFactory;

    protected $commAdminhtmlCatalogCategoryTabRenderVisibilityFactory;

    protected $commAdminhtmlCatalogCategoryTabRenderStatusFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Epicor\Common\Model\Url
     */
    protected $url;

    /**
     * @var \Magento\Framework\Image\Factory
     */
    protected $imageFactor;

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
        \Magento\Framework\Image\Factory $imageFactor
    ) {
        $this->messageManager = $messageManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->imageFactory = $imageFactory;
        $this->imageFactor = $imageFactor;
        $this->quoteQuoteAddressFactory = $quoteQuoteAddressFactory;
        $this->commAdminhtmlCatalogCategoryTabRenderVisibilityFactory = $commAdminhtmlCatalogCategoryTabRenderVisibilityFactory;
        $this->commAdminhtmlCatalogCategoryTabRenderStatusFactory = $commAdminhtmlCatalogCategoryTabRenderStatusFactory;
        $this->scopeConfig = $scopeConfig;
        $this->commHelper = $commHelper;
        $this->storeManager = $storeManager;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->generic = $generic;
        $this->request = $request;
        $this->urlInterface = $urlInterface;
        $this->commProductHelper = $commProductHelper;
        $this->registry = $registry;
        $this->configConfigSourceYesno = $configConfigSourceYesno;
        $this->commConfigSourceCustomertypes = $commConfigSourceCustomertypes;
        $this->listsResourceListModelWebsiteCollectionFactory = $listsResourceListModelWebsiteCollectionFactory;
        $this->listsResourceListModelStoreGroupCollectionFactory = $listsResourceListModelStoreGroupCollectionFactory;
        $this->listsConfigSourceLists = $listsConfigSourceLists;
        $this->commonHelper = $commonHelper;
        $this->commMessageRequestAstFactory = $commMessageRequestAstFactory;
        $this->checkoutCart = $checkoutCart;
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->checkoutSession = $checkoutSession;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->commMessageRequestGorFactory = $commMessageRequestGorFactory;
        $this->commMessageRequestBsvFactory = $commMessageRequestBsvFactory;
        $this->commResourceErpMappingPaymentCollectionFactory = $commResourceErpMappingPaymentCollectionFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->salesOrderPayment = $salesOrderPayment;
        $this->commErpMappingPayment = $commErpMappingPayment;
        $this->catalogRuleRuleFactory = $catalogRuleRuleFactory;
        $this->commMessagingCustomerHelper = $commMessagingCustomerHelper;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->commMessageRequestGqrFactory = $commMessageRequestGqrFactory;
        $this->commProductImageSyncHelper = $commProductImageSyncHelper;
        $this->logger = $logger;
        $this->listsListModelWebsiteFactory = $listsListModelWebsiteFactory;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->listsListModelStoreGroupFactory = $listsListModelStoreGroupFactory;
        $this->commMessageRequestSynFactory = $commMessageRequestSynFactory;
        $this->backendSession = $backendSession;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->commResourceErpMappingCountryCollectionFactory = $commResourceErpMappingCountryCollectionFactory;
        $this->commEntityregHelper = $commEntityregHelper;
        $this->eventManager = $eventManager;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->commReturnsHelper = $commReturnsHelper;
        $this->directoryHelper = $directoryHelper;
        $this->eavEntityAttributeSetFactory = $eavEntityAttributeSetFactory;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->response = $response;
        $this->urlBuilder = $urlBuilder;
        $this->directoryList = $directoryList;
        $this->url = $url;
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
    }

    /**
     * trims any branding info
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $storeGroup = $observer->getEvent()->getStoreGroup();
        /* @var $storeGroup Mage_Core_Model_Store_Group */
        $storeGroup->setEccCompany(trim($storeGroup->getEccCompany()));
        $storeGroup->setEccSite(trim($storeGroup->getEccSite()));
        $storeGroup->setEccWarehouse(trim($storeGroup->getEccWarehouse()));
        $storeGroup->setEccGroup(trim($storeGroup->getEccGroup()));

        $postData = $this->request->getPost();

        //process brand image
        $mediaPath = 'brandimage';
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //$baseUrl = Mage::getBaseDir('media');
        $baseUrl = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        //M1 > M2 Translation End
        $path = $baseUrl . DIRECTORY_SEPARATOR . $mediaPath;
        if (isset($postData['ecc_brandimage'])) {                                  // delete image if requested
            if (isset($postData['ecc_brandimage']['delete'])) {
                if ($postData['ecc_brandimage']['delete']) {
                    if ($postData['brandimage']['value']) {
                        if (file_exists($baseUrl . DIRECTORY_SEPARATOR . $postData['ecc_brandimage']['value'])) {
                            unlink($baseUrl . DIRECTORY_SEPARATOR . $postData['ecc_brandimage']['value']);
                        }
                    }
                    $storeGroup->setEccBrandimage('');
                    return;
                }
            }
        }

        if (isset($_FILES['ecc_brandimage']['name']) and ( file_exists($_FILES['ecc_brandimage']['tmp_name']))) {
            try {
                $uploader = $this->uploaderFactory->create(['fileId' => 'ecc_brandimage']);
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'svg'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $imageParts = explode('.', $_FILES['ecc_brandimage']['name']);
                $imageExtension = array_pop($imageParts);
                $brandImageFileName = $storeGroup->getName() . "." . $imageExtension;
                $brandImageFileName = str_replace(' ', '_', $brandImageFileName);
                $uploader->save($path, $brandImageFileName);
                $_image = $this->imageFactor->create($path . DIRECTORY_SEPARATOR . $brandImageFileName);

                $_image->open($path . DIRECTORY_SEPARATOR . $brandImageFileName);
                $_image->constrainOnly(true);
                $_image->keepAspectRatio(true);
                $_image->keepFrame(true);
                $_image->keepTransparency(true);
                $_image->save($path, $brandImageFileName);

                $storeGroup->setEccBrandimage(trim($brandImageFileName));
                $storeGroup->save();                                        // if this isn't here, the brandimage location is not saved 
            } catch (\Exception $e) {
                $this->logger->debug('--- error saving uploaded brand image ---');
                $this->logger->debug($e);
            }
        }

        if (isset($postData['ecc_allowed_customer_types'])) {
            $storeGroup->setEccAllowedCustomerTypes(implode(',', $postData['ecc_allowed_customer_types']));
        } else {
            $storeGroup->setEccAllowedCustomerTypes('');
        }
    }

}