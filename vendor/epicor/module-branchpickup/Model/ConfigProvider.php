<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Epicor\BranchPickup\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url as CustomerUrlManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface as ShippingMethodManager;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CustomerUrlManager
     */
    private $customerUrlManager;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteItemRepository
     */
    private $quoteItemRepository;

    /**
     * @var ShippingMethodManager
     */
    private $shippingMethodManager;

    /**
     * @var ConfigurationPool
     */
    private $configurationPool;

    /**
     * @param QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var LocaleFormat
     */
    protected $localeFormat;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $addressConfig;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var \Magento\Directory\Model\Country\Postcode\ConfigInterface
     */
    protected $postCodesConfig;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var Cart\ImageProvider
     */
    protected $imageProvider;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingMethodConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param CheckoutHelper $checkoutHelper
     * @param Session $checkoutSession
     * @param CustomerRepository $customerRepository
     * @param CustomerSession $customerSession
     * @param CustomerUrlManager $customerUrlManager
     * @param HttpContext $httpContext
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param QuoteItemRepository $quoteItemRepository
     * @param ShippingMethodManager $shippingMethodManager
     * @param ConfigurationPool $configurationPool
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param LocaleFormat $localeFormat
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param FormKey $formKey
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \Magento\Directory\Model\Country\Postcode\ConfigInterface $postCodesConfig
     * @param Cart\ImageProvider $imageProvider
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Shipping\Model\Config $shippingMethodConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param UrlInterface $urlBuilder
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    
    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $commResourceLocationCollectionFactory;
    
    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;    
    
    
    protected $customerAddressFactory;
    
    public function __construct(
        CheckoutHelper $checkoutHelper,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory,
        CheckoutSession $checkoutSession,
        CustomerRepository $customerRepository,
        CustomerSession $customerSession,
        CustomerUrlManager $customerUrlManager,
        HttpContext $httpContext,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        QuoteItemRepository $quoteItemRepository,
        ShippingMethodManager $shippingMethodManager,
        ConfigurationPool $configurationPool,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        LocaleFormat $localeFormat,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        FormKey $formKey,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\Directory\Model\Country\Postcode\ConfigInterface $postCodesConfig,
        \Magento\Directory\Helper\Data $directoryHelper,
        CartTotalRepositoryInterface $cartTotalRepository,
        ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shippingMethodConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        UrlInterface $urlBuilder
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->customerUrlManager = $customerUrlManager;
        $this->httpContext = $httpContext;
        $this->quoteRepository = $quoteRepository;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->shippingMethodManager = $shippingMethodManager;
        $this->configurationPool = $configurationPool;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->localeFormat = $localeFormat;
        $this->addressMapper = $addressMapper;
        $this->addressConfig = $addressConfig;
        $this->formKey = $formKey;
        $this->imageHelper = $imageHelper;
        $this->viewConfig = $viewConfig;
        $this->postCodesConfig = $postCodesConfig;
        $this->directoryHelper = $directoryHelper;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->scopeConfig = $scopeConfig;
        $this->shippingMethodConfig = $shippingMethodConfig;
        $this->storeManager = $storeManager;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
        $this->branchPickupHelper = $branchPickupHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $helperbranch = $this->branchPickupHelper;
        /* @var Epicor_BranchPickup_Helper_Data */
        $helperLocation = $helperbranch->getLocationHelper();
        $quote = $this->checkoutSession->getQuote();
        $output = [];
        if ($helperbranch->isBranchPickupAvailable()
            || $helperLocation->getLocationStyle() == "inventory_view"
            || $quote->isSalesRep()
        ) {
            $output['formKey'] = $this->formKey->getFormKey();
            $output['customerBranchPickupData'] = $this->getCustomerData();
            $selectBranchcode = false;
            if ($helperbranch->isBranchPickupAvailable()) {
                $selectBranchcode = $helperbranch->getSelectedBranch();
            }
            $output['selectedBranch'] = $selectBranchcode;
            $output['carrierTitle'] = $this->branchPickupTitle();
            $output['carrierMethodTitle'] = $this->branchPickupMethodTitle();
            $output['defaultShippingAddress'] = $this->defaultShippingAddress();
            $output['branchPickupCode'] = \Epicor\BranchPickup\Model\Carrier\Epicorbranchpickup::ECC_BRANCHPICKUP;
            $output['inventoryView'] = ($helperLocation->getLocationStyle() == "inventory_view") ? true : false;
        }
        return $output;
    }
    
    public  function defaultShippingAddress()
    {
        $billingID =  $this->customerSession->getCustomer()->getDefaultBilling();
        $address =  $this->customerAddressFactory->create()->load($billingID);

    }

    public function branchPickupTitle()
    {
        $title = $this->scopeConfig->getValue("carriers/eccbranchpickup/title", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $title;
    } 
    
    public function branchPickupMethodTitle()
    {
        $title = $this->scopeConfig->getValue("carriers/eccbranchpickup/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $title;
    }     

    /**
     * Retrieve customer data
     *
     * @return array
     */
    private function getCustomerData()
    {
        $customerData = [];
      
        $locationIds = $this->_getSelected();
        $collection = $this->commResourceLocationCollectionFactory->create();
        $collection->addFieldToFilter('code', array(
            'in' => $locationIds
        ));
        $collection->getSelect()->order('sort_order ASC');
        $locationAddress = $collection->getData();
        foreach ($locationAddress as $key => $address) {
            $customerData['addresses'][$key] = $this->formatBranchAddress($address['code']);
        }
        return $customerData;
    }

    
    /**
     * Used in grid to return selected customers values.
     */
    protected function _getSelected()
    {
        $helperbranch = $this->branchPickupHelper;
        /* @var Epicor_BranchPickup_Helper_Data */
        return array_keys($helperbranch->getSelected());
    }    
    
    
    protected  function formatBranchAddress($locationCode)
    {
        $helper = $this->branchPickupHelper;
        /* var Epicor_BranchPickup_Helper_Data  */
        $getData = $helper->getPickupAddress($locationCode,true);
        return $getData;
    }

   
}