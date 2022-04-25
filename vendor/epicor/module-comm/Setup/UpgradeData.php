<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Comm\Setup;


use Epicor\Common\Helper\Setup;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Zend_Validate_Exception;

class UpgradeData implements UpgradeDataInterface
{

    const XML_PATH_SHIPPING_CREATE = 'epicor_comm_field_mapping/cus_mapping/cus_create_addresses_shipping';
    const XML_PATH_BILLING_CREATE = 'epicor_comm_field_mapping/cus_mapping/cus_create_addresses_billing';
    const XML_PATH_ADDRESS_CREATE = 'epicor_comm_field_mapping/cus_mapping/cus_create_addresses';
    const XML_PATH_LIMIT_NAME_LENGTH = 'customer/address/limit_name_length';
    const XML_PATH_LIMIT_LASTNAME_LENGTH = 'customer/address/limit_lastname_length';
    const XML_PATH_LIMIT_COMPANY_LENGTH = 'customer/address/limit_company_length';
    const XML_PATH_CUSTOMER_CAPTCHA_FORMS = 'customer/captcha/forms';

    protected $commonHelperSetup;

    /**
     * Customer setup factory.
     *
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * @var string[][]
     */
    private $messagesList = [
            'customer' => ['CUS', 'CUCO', 'CAD', 'CXR', 'CRRC', 'LOC', 'CUSR', 'CURP', 'CUPG', 'CCCN'],
            'part' => ['STK', 'STG', 'SGP', 'STT', 'ALT', 'CPN', 'PAC'],
            'sales' => ['SOU', 'CREU', 'GQR'],
            'supplier' => ['SUSP', 'SUCO'],
            'misce' => ['FREQ', 'FSUB']
        ];

    /**
     * @var array[]
     */
    private $simpleMessages = [
        [
            'label' => 'Customers',
            'value' => [
                'cus' => 'CUS',
                'cad' => 'CAD',
                'cuco' => 'CUCO'
            ]
        ],
        [
            'label' => 'Locations',
            'value' => [
                'loc' => 'LOC'
            ]
        ],
        [
            'label' => 'Products',
            'value' => [
                'pac' => 'PAC',
                'stk' => 'STK',
                'stt' => 'STT',
                'alt' => 'ALT'
            ]
        ],
        [
            'label' => 'Quotes',
            'value' => [
                'gqr' => 'GQR'
            ]
        ],
        [
            'label' => 'Lists',
            'value' => [
                'cupg' => 'CUPG',
                'cccn' => 'CCCN',
                'curp' => 'CURP'
            ]
        ],
        [
            'label' => 'Sales Reps',
            'value' => [
                'cusr' => 'CUSR'
            ]
        ],
        [
            'label' => 'Categories',
            'value' => [
                'stg' => 'STG'
            ]
        ],
        [
            'label' => 'Product Catalog',
            'value' => [
                'sgp' => 'SGP'
            ]
        ],
        [
            'label' => 'Exchange Rates',
            'value' => [
                'cxr' => 'CXR'
            ]
        ],
        [
            'label' => 'Customer Part Numbers',
            'value' => [
                'cpn' => 'CPN'
            ]
        ],
        [
            'label' => 'Order Status Updates',
            'value' => [
                'sou' => 'SOU'
            ]
        ],
        [
            'label' => 'Return Reason Codes',
            'value' => [
                'crrc' => 'CRRC'
            ]
        ],
        [
            'label' => 'Return Status Updates',
            'value' => [
                'creu' => 'CREU'
            ]
        ],
        [
            'label' => 'Suppliers',
            'value' => [
                'susp' => 'SUSP',
                'suco' => 'SUCO'
            ]
        ]
    ];


    /**
     * UpgradeData constructor.
     *
     * @param Setup $commonHelperSetup Setup helper class.
     * @param CacheInterface $cacheManager Cache.
     * @param ScopeConfigInterface $scopeConfig Config Class.
     * @param ResourceModel\Config $resourceConfig Config Resource Class.
     * @param CustomerSetupFactory $customerSetupFactory Customer setup factory.
     */
    public function __construct(
        Setup $commonHelperSetup,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        CustomerSetupFactory $customerSetupFactory
    )
    {
        $this->commonHelperSetup = $commonHelperSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->cache = $cacheManager;

    }//end __construct()


    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.7.2.1', '<')) {
            $helper = $this->commonHelperSetup;
            $helper->addAccessElement('Epicor_Common', 'Account', 'changeForgotten', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Comm', 'Locations', 'addToCartFromMyOrdersWidget', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Locations', '*', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Comm', 'Configurableproducts', '*', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Comm', 'Store', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Store', 'selector', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Store', 'select', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'duplicate', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'syncContact', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Elements', '*', '*', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Esdm', '*', '*', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Aga', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'save', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'pricingrules', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'manage', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'reset', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'pricingrulespost', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'deletepricingrule', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'hierarchy', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'salesreps', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'salesrepadd', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'childaccountadd', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'unlinkchildaccount', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'erpaccounts', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'erpaccountsgrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'erpaccountspost', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'unlinkSalesRep', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account_manage', 'deleteSalesRep', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Account', 'masqueradepopup', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Crqs', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Crqs', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Crqs', 'details', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Crqs', 'update', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Crqs', 'exportCsv', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Crqs', 'exportXml', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'saveContact', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'progress', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'shippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'review', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'success', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'failure', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'getAdditional', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'getAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'saveMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'saveBilling', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'saveShipping', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'saveShippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'savePayment', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Onepage', 'saveOrder', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Order', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Order', 'history', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Order', 'viewOld', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Order', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Order', 'invoice', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Order', 'shipment', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Order', 'creditmemo', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Order', 'reorder', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Order', 'print', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Order', 'printInvoice', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Order', 'printShipment', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Order', 'printCreditmemo', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Promo_catalog', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Promo_catalog', 'newConditionHtml', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Promo_widget', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Promo_widget', 'chooser', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_SalesRep', 'Promo_widget', 'categoriesJson', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Masquerade', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Grid', 'clear', '', 'Access', 1, 0);
            $helper->addAccessElement('Magento_Cms', 'Index', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Cms', 'Index', 'defaultIndex', '', 'Access', 1, 0);
            $helper->addAccessElement('Magento_Cms', 'Index', 'noRoute', '', 'Access', 1, 0);
            $helper->addAccessElement('Magento_Cms', 'Index', 'defaultNoRoute', '', 'Access', 1, 0);
            $helper->addAccessElement('Magento_Cms', 'Index', 'noCookies', '', 'Access', 1, 0);
            $helper->addAccessElement('Magento_Cms', 'Index', 'defaultNoCookies', '', 'Access', 1, 0);
            $helper->addAccessElement('Magento_Cms', 'Page', 'view', '', 'Access', 1, 0);
            $helper->addAccessElement('Magento_Customer', 'Account', 'login', '', 'Access', 1, 0);
            $helper->addAccessElement('Magento_Customer', 'Account', 'loginPost', '', 'Access', 1, 0);
            $helper->addAccessElement('Magento_Customer', 'Account', 'logout', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Customer', 'Account', 'logoutSuccess', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Customer', 'Account', 'create', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Customer', 'Account', 'createPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Customer', 'Account', 'confirm', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Customer', 'Account', 'confirmation', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Customer', 'Account', 'forgotPassword', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Customer', 'Account', 'forgotPasswordPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Customer', 'Account', 'resetPassword', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Customer', 'Account', 'resetPasswordPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_CatalogSearch', 'Ajax', 'suggest', '', 'Access', 1, 0);
            $helper->addAccessElement('Magento_CatalogSearch', 'Term', 'popular', '', 'Access', 1, 0);
            $helper->addAccessElement('Magento_Rss', 'Catalog', 'new', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Rss', 'Catalog', 'special', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Rss', 'Catalog', 'salesrule', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Rss', 'Catalog', 'tag', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Rss', 'Catalog', 'notifystock', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Rss', 'Catalog', 'review', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Rss', 'Catalog', 'category', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Rss', 'Index', 'index', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Rss', 'Index', 'nofeed', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Rss', 'Index', 'wishlist', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Rss', 'Order', 'new', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Rss', 'Order', 'customer', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Rss', 'Order', 'status', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_ProductAlert', 'Add', 'testObserver', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_ProductAlert', 'Add', 'price', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_ProductAlert', 'Add', 'stock', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_ProductAlert', 'Unsubscribe', 'price', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_ProductAlert', 'Unsubscribe', 'priceAll', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_ProductAlert', 'Unsubscribe', 'stock', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_ProductAlert', 'Unsubscribe', 'stockAll', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Api', 'Index', 'index', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Api', 'Soap', 'index', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Api', 'Xmlrpc', 'index', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Oauth', 'Authorize', 'index', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Oauth', 'Authorize', 'simple', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Persistent', 'Index', 'saveMethod', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Persistent', 'Index', 'expressCheckout', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_XmlConnect', 'Cart', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cart', 'update', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cart', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cart', 'delete', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cart', 'coupon', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cart', 'addGiftcard', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cart', 'removeGiftcard', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cart', 'removeStoreCredit', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cart', 'info', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cart', 'shoppingCart', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cart', 'configure', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cart', 'updateItemOptions', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', 'category', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', 'categoryDetails', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', 'filters', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', 'product', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', 'productView', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', 'productOptions', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', 'productGallery', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', 'productReviews', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', 'productReview', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', 'search', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', 'searchDetails', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', 'searchSuggest', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', 'sendEmail', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'newBillingAddressForm', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'newShippingAddressForm', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'billingAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'saveBillingAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'shippingAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'saveShippingAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'shippingMethods', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'shippingMethodsList', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'saveShippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'saveMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'paymentMethodList', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'paymentMethods', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'savePayment', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'orderReview', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'orderSummary', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'saveOrder', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'addressMassaction', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', 'saveAddressInfo', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cms', 'page', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cms', 'sentinelSecure', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Configuration', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'login', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'logout', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'form', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'edit', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'save', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'forgotPassword', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'address', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'addressForm', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'deleteAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'saveAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'orderList', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'orderDetails', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'isLoggined', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'storeCredit', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'giftcardCheck', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'giftcardRedeem', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'downloads', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', 'checkoutRegistration', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Homebanners', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Index', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Localization', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'OfflineCatalog', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mecl', 'start', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mecl', 'return', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mecl', 'review', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mecl', 'orderReview', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mecl', 'shippingMethods', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mecl', 'saveShippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mecl', 'placeOrder', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mecl', 'cancel', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mep', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mep', 'saveShippingAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mep', 'shippingMethods', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mep', 'saveShippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mep', 'cartTotals', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Mep', 'saveOrder', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Pbridge', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Pbridge', 'result', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Pbridge', 'output', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Review', 'form', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Review', 'save', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Wishlist', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Wishlist', 'details', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Wishlist', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Wishlist', 'remove', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Wishlist', 'clear', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Wishlist', 'update', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Wishlist', 'cart', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Common', 'Account', 'error', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'login', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'loginPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'logout', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'logoutSuccess', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'create', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'createPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'confirm', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'confirmation', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'forgotPassword', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'forgotPasswordPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'resetPassword', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'resetPasswordPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'edit', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'Account', 'editPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_Cms', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Cms', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Cms', 'Page', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Account', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Account', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Account', 'changeForgotten', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Account', 'edit', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Account', 'editPost', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Address', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Address', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Address', 'edit', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Address', 'new', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Address', 'form', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Address', 'formPost', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Address', 'delete', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Review', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Review', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Customer', 'Review', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_CatalogSearch', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_CatalogSearch', 'Advanced', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_CatalogSearch', 'Advanced', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_CatalogSearch', 'Advanced', 'result', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_CatalogSearch', 'Ajax', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_CatalogSearch', 'Result', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_CatalogSearch', 'Result', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_CatalogSearch', 'Term', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Rss', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Rss', 'Catalog', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Rss', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Rss', 'Order', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_ProductAlert', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_ProductAlert', 'Add', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_ProductAlert', 'Unsubscribe', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Api', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Api', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Api', 'Soap', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Api', 'V2_soap', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Api', 'V2_soap', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Api', 'Xmlrpc', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', 'Authorize', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', 'Authorize', 'confirm', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', 'Authorize', 'confirmSimple', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', 'Authorize', 'reject', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', 'Authorize', 'rejectSimple', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', 'Customer_token', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', 'Customer_token', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', 'Customer_token', 'revoke', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', 'Customer_token', 'delete', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', 'Initiate', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', 'Initiate', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', 'Token', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Oauth', 'Token', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Persistent', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Persistent', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Persistent', 'Index', 'unsetCookie', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', '*', '*', '', 'Access', 1, 1);
            $helper->addAccessElement('Magento_XmlConnect', 'Cart', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Catalog', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Checkout', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Cms', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Configuration', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Customer', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Homebanners', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Localization', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Offlinecatalog', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'start', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'return', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'review', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'orderReview', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'shippingMethods', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'saveShippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'placeOrder', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'cancel', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mep', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mep', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mep', 'saveShippingAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mep', 'shippingMethods', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mep', 'saveShippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mep', 'cartTotals', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mep', 'saveOrder', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Pbridge', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Review', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Wishlist', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Core', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Core', 'Ajax', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Core', 'Ajax', 'translate', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Core', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Core', 'Index', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Directory', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Directory', 'Currency', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Directory', 'Currency', 'switch', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Category', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Category', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Index', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Product_compare', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Product_compare', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Product_compare', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Product_compare', 'remove', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Product_compare', 'clear', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Product', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Product', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Product', 'gallery', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Product', 'image', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Seo_sitemap', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Seo_sitemap', 'category', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Catalog', 'Seo_sitemap', 'product', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Billing_agreement', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Billing_agreement', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Billing_agreement', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Billing_agreement', 'startWizard', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Billing_agreement', 'returnWizard', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Billing_agreement', 'cancelWizard', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Billing_agreement', 'cancel', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Download', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Download', 'downloadProfileCustomOption', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Download', 'downloadCustomOption', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Guest', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Guest', 'form', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Guest', 'printInvoice', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Guest', 'printShipment', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Guest', 'printCreditmemo', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Guest', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Guest', 'invoice', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Guest', 'shipment', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Guest', 'creditmemo', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Guest', 'reorder', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Guest', 'print', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Order', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Order', 'history', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Order', 'viewOld', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Order', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Order', 'invoice', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Order', 'shipment', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Order', 'creditmemo', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Order', 'reorder', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Order', 'print', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Order', 'printInvoice', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Order', 'printShipment', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Order', 'printCreditmemo', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Recurring_profile', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Recurring_profile', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Recurring_profile', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Recurring_profile', 'history', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Recurring_profile', 'orders', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Recurring_profile', 'vendor', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Recurring_profile', 'updateState', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sales', 'Recurring_profile', 'updateProfile', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Shipping', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Shipping', 'Shipping', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Shipping', 'Shipping', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Shipping', 'Tracking', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Shipping', 'Tracking', 'ajax', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Shipping', 'Tracking', 'popup', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paygate', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paygate', 'Authorizenet_payment', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paygate', 'Authorizenet_payment', 'cancel', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Cart', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Cart', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Cart', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Cart', 'addgroup', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Cart', 'configure', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Cart', 'updateItemOptions', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Cart', 'updatePost', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Cart', 'delete', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Cart', 'estimatePost', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Cart', 'estimateUpdatePost', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Cart', 'couponPost', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Cart', 'ajaxDelete', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Cart', 'ajaxUpdate', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Index', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping_address', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping_address', 'newShipping', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping_address', 'shippingSaved', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping_address', 'editShipping', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping_address', 'editShippingPost', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping_address', 'selectBilling', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping_address', 'newBilling', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping_address', 'editAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping_address', 'editBilling', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping_address', 'setBilling', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping_address', 'saveBilling', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'login', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'register', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'addresses', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'addressesPost', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'backToAddresses', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'removeItem', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'shipping', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'backToShipping', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'shippingPost', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'billing', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'backToBilling', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'overview', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'overviewPost', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Multishipping', 'success', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'progress', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'shippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'review', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'success', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'failure', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'getAdditional', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'getAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'saveMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'saveBilling', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'saveShipping', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'saveShippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'savePayment', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Checkout', 'Onepage', 'saveOrder', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Bml', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Bml', 'start', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Express', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Express', 'start', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Express', 'shippingOptionsCallback', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Express', 'cancel', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Express', 'return', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Express', 'review', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Express', 'edit', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Express', 'saveShippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Express', 'updateShippingMethods', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Express', 'placeOrder', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Hostedpro', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Hostedpro', 'return', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Hostedpro', 'cancel', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Ipn', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Ipn', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflow', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflow', 'cancelPayment', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflow', 'returnUrl', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflow', 'form', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflow', 'silentPost', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', 'cancelPayment', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', 'returnUrl', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', 'form', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', 'silentPost', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', 'start', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', 'shippingOptionsCallback', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', 'cancel', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', 'return', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', 'review', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', 'edit', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', 'saveShippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', 'updateShippingMethods', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Payflowadvanced', 'placeOrder', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Standard', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Standard', 'redirect', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Paypal', 'Standard', 'cancel', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Poll', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Poll', 'Vote', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Poll', 'Vote', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_GoogleCheckout', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Review', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Review', 'Customer', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Review', 'Customer', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Review', 'Customer', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Review', 'Product', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Review', 'Product', 'post', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Review', 'Product', 'list', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Review', 'Product', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Tag', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Tag', 'Customer', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Tag', 'Customer', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Tag', 'Customer', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Tag', 'Customer', 'edit', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Tag', 'Customer', 'remove', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Tag', 'Customer', 'save', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Tag', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Tag', 'Index', 'save', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Tag', 'List', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Tag', 'List', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Tag', 'Product', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Tag', 'Product', 'list', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Index', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Index', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Index', 'configure', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Index', 'updateItemOptions', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Index', 'update', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Index', 'remove', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Index', 'cart', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Index', 'fromcart', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Index', 'share', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Index', 'send', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Index', 'downloadCustomOption', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Index', 'allcart', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Shared', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Shared', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Shared', 'cart', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Wishlist', 'Shared', 'allcart', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_GiftMessage', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_GiftMessage', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_GiftMessage', 'Index', 'save', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Contacts', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Contacts', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Contacts', 'Index', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Contacts', 'Index', 'post', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sendfriend', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sendfriend', 'Product', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sendfriend', 'Product', 'send', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Sendfriend', 'Product', 'sendmail', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Authorizenet', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Authorizenet', 'Directpost_payment', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Authorizenet', 'Directpost_payment', 'response', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Authorizenet', 'Directpost_payment', 'redirect', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Authorizenet', 'Directpost_payment', 'place', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Authorizenet', 'Directpost_payment', 'returnQuote', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'form', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'new', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'edit', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'wysiwyg', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'grid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'gridOnly', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'categories', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'options', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'related', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'upsell', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'crosssell', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'relatedGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'upsellGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'crosssellGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'superGroup', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'superGroupGridOnly', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'reviews', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'superConfig', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'bundles', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'validate', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'categoriesJson', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'save', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'duplicate', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'delete', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'tagGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'alertsPriceGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'alertsStockGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'addCustomersToAlertQueue', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'addAttribute', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'created', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'massDelete', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'massStatus', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'tagCustomerGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'quickCreate', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'showUpdateResult', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'denied', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'noroute', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'has', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'getFull', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'get', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Product_edit', 'noCookies', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Selection', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Selection', 'search', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Selection', 'grid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Selection', 'denied', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Selection', 'noroute', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Selection', 'has', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Selection', 'getFull', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Selection', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Selection', 'get', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Bundle', 'Selection', 'noCookies', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Captcha', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Captcha', 'Refresh', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Captcha', 'Refresh', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Centinel', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Centinel', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Centinel', 'Index', 'authenticationStart', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Centinel', 'Index', 'authenticationComplete', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', 'Process', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', 'Process', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', 'Process', 'run', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', 'Process', 'recompile', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', 'Process', 'disable', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', 'Process', 'enable', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', 'Process', 'denied', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', 'Process', 'noroute', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', 'Process', 'has', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', 'Process', 'getFull', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', 'Process', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', 'Process', 'get', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Compiler', 'Process', 'noCookies', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Newsletter', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Newsletter', 'Manage', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Newsletter', 'Manage', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Newsletter', 'Manage', 'save', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Newsletter', 'Subscriber', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Newsletter', 'Subscriber', 'new', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Newsletter', 'Subscriber', 'confirm', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Newsletter', 'Subscriber', 'unsubscribe', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Customer', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Customer', 'products', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Download', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Download', 'sample', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Download', 'linkSample', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Download', 'link', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'File', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'File', 'upload', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'File', 'denied', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'File', 'noroute', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'File', 'has', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'File', 'getFull', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'File', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'File', 'get', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'File', 'noCookies', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'form', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'link', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'new', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'edit', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'wysiwyg', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'grid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'gridOnly', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'categories', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'options', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'related', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'upsell', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'crosssell', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'relatedGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'upsellGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'crosssellGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'superGroup', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'superGroupGridOnly', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'reviews', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'superConfig', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'bundles', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'validate', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'categoriesJson', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'save', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'duplicate', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'delete', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'tagGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'alertsPriceGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'alertsStockGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'addCustomersToAlertQueue', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'addAttribute', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'created', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'massDelete', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'massStatus', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'tagCustomerGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'quickCreate', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'showUpdateResult', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'denied', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'noroute', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'has', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'getFull', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'get', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_Downloadable', 'Product_edit', 'noCookies', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'login', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'create', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'loginPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'logout', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'logoutSuccess', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'createPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'confirm', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'confirmation', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'forgotPassword', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'forgotPasswordPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'changeForgotten', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'resetPassword', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'resetPasswordPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'edit', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Customer_account', 'editPost', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Invoice', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Invoice', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Invoice', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Invoice', 'copy', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Order', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Order', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Order', 'edit', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Order', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Order', 'update', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Portal', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Portal', 'register', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Portal', 'registerPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Portal', 'error', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Portal', 'login', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Common', 'Account', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Common', 'Account', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Common', 'Account', 'dashboard', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Common', 'Account', 'encodedata', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Common', 'File', '*', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Common', 'File', 'request', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Common', 'Sales_order', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Common', 'Sales_order', 'reorder', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'estimatePost', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'estimateUpdatePost', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'couponPost', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'csvupload', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'importProductCsv', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'addgroup', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'configure', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'updateItemOptions', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'updatePost', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'delete', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'ajaxDelete', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Cart', 'ajaxUpdate', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Configurableproducts', 'stockandprice', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Configurator', '*', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Comm', 'Configurator', 'editewa', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Configurator', 'badurl', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Configurator', 'error', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Configurator', 'reorderewa', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Configurator', 'loadewa', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Configurator', 'ewacss', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Configurator', 'ewacomplete', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', '*', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'imagecleanup', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'crru', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'submitfiles', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'offlineorders', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'schedulemsq', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'schedulesod', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'synlogcleanup', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'scheduleimage', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'postdebug', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'responder', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'logclean', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'queueclean', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'indexproduct', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'pk', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'licfor', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'updatesvn', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'enableaga', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'disableaga', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'clearcache', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'postdata', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Data', 'generateCartCsv', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Locations', 'filter', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Locations', 'addToCartFromWishlist', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Masquerade', 'masquerade', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Message', '*', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Comm', 'Message', 'msq', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Message', 'gor', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Message', 'csns', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Message', 'cim', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Message', 'cdm', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Message', 'crqu', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'saveShippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'saveShippingDates', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'saveBilling', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'savePayment', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'billingpopup', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'billingPopupGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'shippingPopupGrid', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'shippingPopup', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'grid', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'saveShipping', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'progress', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'shippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'review', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'success', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'failure', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'getAdditional', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'getAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'saveMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Onepage', 'saveOrder', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Quickadd', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Quickadd', 'autocomplete', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Quickadd', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Quickadd', 'nonAutoLocations', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Remotelinks', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Remotelinks', 'fetch', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'index', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'guestLogin', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'createReturn', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'findReturn', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'updateReference', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'addProduct', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'findProduct', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'saveLines', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'saveAttachments', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'saveNotes', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'saveReview', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'delete', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'list', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'createReturnFromDocument', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'configureproduct', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Returns', 'submitconfiguration', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Test', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Test', 'gj', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Test', 'pk', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Test', 'stkin', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Test', 'stkout', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Test', 'cusin', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Test', 'cusout', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Test', 'updload', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Test', 'request', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Test', 'sf', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Test', 'generateFileChain', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Comm', 'Test', 'updateTranslationCsvFile', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_ErpSimulator', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_ErpSimulator', 'Request', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_ErpSimulator', 'Request', 'index', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Esdm', 'Payment', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Esdm', 'Payment', 'opcsavereview', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Esdm', 'Savedcards', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Esdm', 'Savedcards', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Esdm', 'Savedcards', 'delete', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'new', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'edit', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'validate', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'save', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'delete', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'wysiwygPlugin', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'denied', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'noroute', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'has', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'getFull', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'get', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_FlexiTheme', 'System_variable', 'noCookies', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_ProductFeed', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_ProductFeed', 'Generate', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_ProductFeed', 'Generate', 'googlefeed', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_QuickOrderPad', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_QuickOrderPad', 'Form', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_QuickOrderPad', 'Form', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_QuickOrderPad', 'Form', 'results', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_QuickOrderPad', 'Form', 'configclear', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', 'Manage', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', 'Manage', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', 'Manage', 'view', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', 'Manage', 'accept', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', 'Manage', 'reject', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', 'Manage', 'saveDuplicate', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', 'Manage', 'update', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', 'Manage', 'newnote', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', 'Manage', 'resubmit', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', 'Request', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', 'Request', 'test', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', 'Request', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Quotes', 'Request', 'submit', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Verifone', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Verifone', 'Payment', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Verifone', 'Payment', 'opcsavereview', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Verifone', 'Payment', 'bankredirect', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Verifone', 'Payment', 'payerauth', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Verifone', 'Savedcards', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Verifone', 'Savedcards', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Verifone', 'Savedcards', 'test', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Verifone', 'Savedcards', 'delete', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Moneybookers', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Moneybookers', 'activateemail', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Moneybookers', 'checkemail', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Moneybookers', 'checksecret', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Moneybookers', 'denied', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Moneybookers', 'noroute', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Moneybookers', 'has', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Moneybookers', 'getFull', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Moneybookers', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Moneybookers', 'get', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Moneybookers', 'noCookies', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Processing', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Processing', 'placeform', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Processing', 'payment', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Processing', 'success', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Processing', 'cancel', '', 'Access', 0, 0);
            $helper->addAccessElement('Phoenix_Moneybookers', 'Processing', 'status', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'start', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'return', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'review', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'orderReview', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'shippingMethods', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'saveShippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'placeOrder', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mecl', 'cancel', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mep', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mep', 'saveShippingAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mep', 'shippingMethods', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mep', 'saveShippingMethod', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mep', 'cartTotals', '', 'Access', 0, 0);
            $helper->addAccessElement('Magento_XmlConnect', 'Paypal_mep', 'saveOrder', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Elements', 'Payment', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Elements', 'Payment', 'opcsavereview', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Elements', 'Payment', 'setupreturn', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Elements', 'Savedcards', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Elements', 'Savedcards', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Elements', 'Savedcards', 'delete', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Elements', 'Payment', 'bankredirect', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Elements', 'Payment', 'setupreturnAction', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Access_management', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Access_management', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Access_management', 'addgroup', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Access_management', 'editgroup', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Access_management', 'savegroup', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'index', 'account_information', 'view', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'index', 'period_balances', 'view', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'index', 'aged_balances', 'view', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'index', 'shipping_addresses', 'view', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'index', 'contacts', 'view', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'index', 'manage_permissions', 'view', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'saveBillingAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'saveShippingAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'deleteShippingAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'saveContact', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'deleteContact', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'saveErpBillingAddress', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'saveCustomAddressAllowed', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Dashboard', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Dashboard', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Dashboard', 'index', 'customer_account_summary', 'view', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Grid', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Grid', 'shippingsearch', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Grid', 'contactssearch', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Grid', 'orderssearch', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Grid', 'invoicessearch', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Invoices', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Invoices', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Invoices', 'details', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Invoices', 'reorder', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Invoices', 'exportInvoicesCsv', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Invoices', 'exportInvoicesXml', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Orders', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Orders', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Orders', 'details', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Orders', 'reorder', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Orders', 'exportOrdersCsv', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Orders', 'exportOrdersXml', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Payments', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Payments', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Payments', 'exportPaymentsCsv', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Payments', 'exportPaymentsXml', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Returns', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Returns', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Returns', 'details', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Returns', 'exportCsv', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Returns', 'exportXml', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'confirmreject', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'details', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'confirm', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'reject', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'new', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'add', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'update', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'addressdetails', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'exportCsv', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'exportXml', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'lineaddautocomplete', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'linesearch', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'importProductCsv', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'ewaeditcomplete', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'configureproduct', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'submitconfiguration', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rmas', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rmas', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rmas', 'exportRmasCsv', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rmas', 'exportRmasXml', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Servicecalls', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Servicecalls', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Servicecalls', 'exportServicecallsCsv', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Servicecalls', 'exportServicecallsXml', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Shipments', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Shipments', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Shipments', 'details', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Shipments', 'reorder', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Shipments', 'exportShipmentsCsv', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Shipments', 'exportShipmentsXml', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Skus', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Skus', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Skus', 'create', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Skus', 'edit', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Skus', 'save', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Skus', 'delete', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Skus', 'exportToCsv', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Skus', 'exportToXml', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Account', 'index', 'contact_address_permissions', 'view', 0, 0);
            $helper->addAccessElement('Epicor_Customerconnect', 'Rfqs', 'ewacomplete', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Faqs', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Faqs', 'Index', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Faqs', 'Index', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Faqs', 'Index', 'submitVote', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Faqs', 'Index', 'vote', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_B2b', 'Account', 'login', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Account', 'loginPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Account', 'logout', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Account', 'logoutSuccess', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Account', 'create', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Account', 'createPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Account', 'confirm', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Account', 'confirmation', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Account', 'forgotPassword', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Account', 'forgotPasswordPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Account', 'resetPassword', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_B2b', 'Account', 'resetPasswordPost', '', 'Access', 1, 1);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Grid', 'clear', '', 'Access', 1, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', '*', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Access_management', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Access_management', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Access_management', 'addgroup', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Access_management', 'editgroup', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Access_management', 'savegroup', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Account', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Account', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Grid', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Invoices', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Invoices', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Invoices', 'details', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Orders', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Orders', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Orders', 'new', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Orders', 'confirmnew', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Orders', 'changes', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Orders', 'confirmchanges', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Orders', 'details', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Orders', 'update', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Parts', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Parts', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Parts', 'details', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Password', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Password', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Password', 'update', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Payments', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Payments', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Rfq', '*', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Rfq', 'index', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Rfq', 'details', '', 'Access', 0, 0);
            $helper->addAccessElement('Epicor_Supplierconnect', 'Rfq', 'update', '', 'Access', 0, 0);
            $helper->addAccessGroup('ECC Default - Sales Rep Access');
            $helper->addAccessGroup('Customer - Full Access');
            $helper->addAccessGroup('Customerconnect - Full Access');
            $helper->addAccessGroup('Customerconnect - Read Only');
            $helper->addAccessGroup('Customerconnect - Manage Permissions');
            $helper->addAccessGroup('Supplierconnect - Full Access');
            $helper->addAccessGroup('Supplierconnect - Read Only');
            $helper->addAccessGroup('Supplierconnect - Manage Permissions');
            $helper->addAccessRight('ECC Default - Sales Rep - Full Access');
            $helper->addAccessRight('ECC Default - Sales Rep - Masquerade Access');
            $helper->addAccessRight('ECC Default - Sales Rep - CustomerConnect Access');
            $helper->addAccessRight('Customer - Full Access');
            $helper->addAccessRight('Customerconnect - Account Information - Access Page');
            $helper->addAccessRight('Customerconnect - Account Information - Info Pane - View');
            $helper->addAccessRight('Customerconnect - Account Information - Period Balances - View');
            $helper->addAccessRight('Customerconnect - Account Information - Aged Balances - View');
            $helper->addAccessRight('Customerconnect - Account Information - Shipping Addresses - View');
            $helper->addAccessRight('Customerconnect - Account Information - Shipping Addresses - Edit');
            $helper->addAccessRight('Customerconnect - Account Information - Contacts - View');
            $helper->addAccessRight('Customerconnect - Account Information - Contacts - Edit');
            $helper->addAccessRight('Customerconnect - Account Information - Contacts - Manage Permissions');
            $helper->addAccessRight('Customerconnect - Account Information - Billing Addresses - Edit');
            $helper->addAccessRight('Customerconnect - Dashboard - Access Page');
            $helper->addAccessRight('Customerconnect - Dashboard - Account Summary - View');
            $helper->addAccessRight('Customerconnect - Invoices - List Page - View');
            $helper->addAccessRight('Customerconnect - Invoices - Details Page - View');
            $helper->addAccessRight('Customerconnect - Orders - List Page - View');
            $helper->addAccessRight('Customerconnect - Orders - Details Page - View');
            $helper->addAccessRight('Customerconnect - Orders - Re-order');
            $helper->addAccessRight('Customerconnect - Payments - List Page - View');
            $helper->addAccessRight('Customerconnect - RMAs - List Page - View');
            $helper->addAccessRight('Customerconnect - Service Calls - List Page - View');
            $helper->addAccessRight('Customerconnect - Shipments - List Page - View');
            $helper->addAccessRight('Customerconnect - Shipments - Details Page - View');
            $helper->addAccessRight('Customerconnect - Manage Permissions');
            $helper->addAccessRight('Customerconnect - Account Information - Save New Checkout Addresses - Edit');
            $helper->addAccessRight('Customerconnect - Invoices - Re-order');
            $helper->addAccessRight('Customerconnect - Shipments - Re-order');
            $helper->addAccessRight('Customerconnect - Account Information - Contact Address Settings - Edit');
            $helper->addAccessRight('Customerconnect - Rfqs - List Page - View');
            $helper->addAccessRight('Customerconnect - Rfqs - Details Page - View');
            $helper->addAccessRight('Customerconnect - Rfqs - Add/Edit');
            $helper->addAccessRight('Customerconnect - Rfqs - Confirm/Reject');
            $helper->addAccessRight('Customerconnect - Returns - List Page - View');
            $helper->addAccessRight('Customerconnect - Returns - Details Page - View');
            $helper->addAccessRight('Supplierconnect - Dashboard - Access Page');
            $helper->addAccessRight('Supplierconnect - Invoices - List Page - View');
            $helper->addAccessRight('Supplierconnect - Invoices - Details Page - View');
            $helper->addAccessRight('Supplierconnect - Orders - List Page - View');
            $helper->addAccessRight('Supplierconnect - Orders - Details Page - View');
            $helper->addAccessRight('Supplierconnect - Orders - Details Page - Edit');
            $helper->addAccessRight('Supplierconnect - Orders - Confirm New List - View');
            $helper->addAccessRight('Supplierconnect - Orders - Confirm New List - Edit');
            $helper->addAccessRight('Supplierconnect - Orders - Confirm Changes List - View');
            $helper->addAccessRight('Supplierconnect - Orders - Confirm Changes List - Edit');
            $helper->addAccessRight('Supplierconnect - Parts - List Page - View');
            $helper->addAccessRight('Supplierconnect - Parts - Details Page - View');
            $helper->addAccessRight('Supplierconnect - Payments - List Page - View');
            $helper->addAccessRight('Supplierconnect - RFQ - List Page - View');
            $helper->addAccessRight('Supplierconnect - RFQ - Details Page - View');
            $helper->addAccessRight('Supplierconnect - RFQ - Details Page - Edit');
            $helper->addAccessRight('Supplierconnect - Manage Permissions');
            $helper->addAccessGroupRight(1, 1);
            $helper->addAccessGroupRight(1, 2);
            $helper->addAccessGroupRight(1, 3);
            $helper->addAccessGroupRight(2, 4);
            $helper->addAccessGroupRight(3, 5);
            $helper->addAccessGroupRight(4, 5);
            $helper->addAccessGroupRight(3, 6);
            $helper->addAccessGroupRight(4, 6);
            $helper->addAccessGroupRight(3, 7);
            $helper->addAccessGroupRight(4, 7);
            $helper->addAccessGroupRight(3, 8);
            $helper->addAccessGroupRight(4, 8);
            $helper->addAccessGroupRight(3, 9);
            $helper->addAccessGroupRight(4, 9);
            $helper->addAccessGroupRight(3, 10);
            $helper->addAccessGroupRight(3, 11);
            $helper->addAccessGroupRight(4, 11);
            $helper->addAccessGroupRight(3, 12);
            $helper->addAccessGroupRight(3, 13);
            $helper->addAccessGroupRight(3, 14);
            $helper->addAccessGroupRight(3, 15);
            $helper->addAccessGroupRight(4, 15);
            $helper->addAccessGroupRight(3, 16);
            $helper->addAccessGroupRight(4, 16);
            $helper->addAccessGroupRight(3, 17);
            $helper->addAccessGroupRight(4, 17);
            $helper->addAccessGroupRight(3, 18);
            $helper->addAccessGroupRight(4, 18);
            $helper->addAccessGroupRight(3, 19);
            $helper->addAccessGroupRight(4, 19);
            $helper->addAccessGroupRight(3, 20);
            $helper->addAccessGroupRight(4, 20);
            $helper->addAccessGroupRight(3, 21);
            $helper->addAccessGroupRight(3, 22);
            $helper->addAccessGroupRight(4, 22);
            $helper->addAccessGroupRight(3, 23);
            $helper->addAccessGroupRight(4, 23);
            $helper->addAccessGroupRight(3, 24);
            $helper->addAccessGroupRight(4, 24);
            $helper->addAccessGroupRight(3, 25);
            $helper->addAccessGroupRight(4, 25);
            $helper->addAccessGroupRight(3, 26);
            $helper->addAccessGroupRight(4, 26);
            $helper->addAccessGroupRight(5, 27);
            $helper->addAccessGroupRight(3, 28);
            $helper->addAccessGroupRight(3, 29);
            $helper->addAccessGroupRight(3, 30);
            $helper->addAccessGroupRight(5, 31);
            $helper->addAccessGroupRight(3, 32);
            $helper->addAccessGroupRight(4, 32);
            $helper->addAccessGroupRight(3, 33);
            $helper->addAccessGroupRight(4, 33);
            $helper->addAccessGroupRight(3, 34);
            $helper->addAccessGroupRight(3, 35);
            $helper->addAccessGroupRight(3, 36);
            $helper->addAccessGroupRight(4, 36);
            $helper->addAccessGroupRight(3, 37);
            $helper->addAccessGroupRight(4, 37);
            $helper->addAccessGroupRight(6, 38);
            $helper->addAccessGroupRight(7, 38);
            $helper->addAccessGroupRight(6, 39);
            $helper->addAccessGroupRight(7, 39);
            $helper->addAccessGroupRight(6, 40);
            $helper->addAccessGroupRight(7, 40);
            $helper->addAccessGroupRight(6, 41);
            $helper->addAccessGroupRight(7, 41);
            $helper->addAccessGroupRight(6, 42);
            $helper->addAccessGroupRight(7, 42);
            $helper->addAccessGroupRight(6, 43);
            $helper->addAccessGroupRight(6, 44);
            $helper->addAccessGroupRight(7, 44);
            $helper->addAccessGroupRight(6, 45);
            $helper->addAccessGroupRight(6, 46);
            $helper->addAccessGroupRight(7, 46);
            $helper->addAccessGroupRight(6, 47);
            $helper->addAccessGroupRight(6, 48);
            $helper->addAccessGroupRight(7, 48);
            $helper->addAccessGroupRight(6, 49);
            $helper->addAccessGroupRight(7, 49);
            $helper->addAccessGroupRight(6, 50);
            $helper->addAccessGroupRight(7, 50);
            $helper->addAccessGroupRight(6, 51);
            $helper->addAccessGroupRight(7, 51);
            $helper->addAccessGroupRight(6, 52);
            $helper->addAccessGroupRight(7, 52);
            $helper->addAccessGroupRight(6, 53);
            $helper->addAccessGroupRight(8, 54);
            $helper->addAccessRightElementById(1, 13);
            $helper->addAccessRightElementById(2, 74);
            $helper->addAccessRightElementById(3, 75);
            $helper->addAccessRightElementById(4, 2);
            $helper->addAccessRightElementById(4, 5);
            $helper->addAccessRightElementById(4, 6);
            $helper->addAccessRightElementById(4, 7);
            $helper->addAccessRightElementById(4, 8);
            $helper->addAccessRightElementById(4, 9);
            $helper->addAccessRightElementById(4, 12);
            $helper->addAccessRightElementById(4, 13);
            $helper->addAccessRightElementById(4, 14);
            $helper->addAccessRightElementById(4, 15);
            $helper->addAccessRightElementById(4, 16);
            $helper->addAccessRightElementById(4, 17);
            $helper->addAccessRightElementById(4, 18);
            $helper->addAccessRightElementById(4, 19);
            $helper->addAccessRightElementById(4, 20);
            $helper->addAccessRightElementById(4, 21);
            $helper->addAccessRightElementById(4, 22);
            $helper->addAccessRightElementById(4, 23);
            $helper->addAccessRightElementById(4, 24);
            $helper->addAccessRightElementById(4, 25);
            $helper->addAccessRightElementById(4, 26);
            $helper->addAccessRightElementById(4, 27);
            $helper->addAccessRightElementById(4, 28);
            $helper->addAccessRightElementById(4, 29);
            $helper->addAccessRightElementById(4, 30);
            $helper->addAccessRightElementById(4, 31);
            $helper->addAccessRightElementById(4, 32);
            $helper->addAccessRightElementById(4, 33);
            $helper->addAccessRightElementById(4, 34);
            $helper->addAccessRightElementById(4, 35);
            $helper->addAccessRightElementById(4, 36);
            $helper->addAccessRightElementById(4, 37);
            $helper->addAccessRightElementById(4, 38);
            $helper->addAccessRightElementById(4, 39);
            $helper->addAccessRightElementById(4, 40);
            $helper->addAccessRightElementById(4, 41);
            $helper->addAccessRightElementById(4, 42);
            $helper->addAccessRightElementById(4, 43);
            $helper->addAccessRightElementById(4, 44);
            $helper->addAccessRightElementById(4, 45);
            $helper->addAccessRightElementById(4, 46);
            $helper->addAccessRightElementById(4, 47);
            $helper->addAccessRightElementById(4, 48);
            $helper->addAccessRightElementById(4, 49);
            $helper->addAccessRightElementById(4, 50);
            $helper->addAccessRightElementById(4, 51);
            $helper->addAccessRightElementById(4, 52);
            $helper->addAccessRightElementById(4, 53);
            $helper->addAccessRightElementById(4, 54);
            $helper->addAccessRightElementById(4, 55);
            $helper->addAccessRightElementById(4, 56);
            $helper->addAccessRightElementById(4, 57);
            $helper->addAccessRightElementById(4, 58);
            $helper->addAccessRightElementById(4, 59);
            $helper->addAccessRightElementById(4, 60);
            $helper->addAccessRightElementById(4, 61);
            $helper->addAccessRightElementById(4, 62);
            $helper->addAccessRightElementById(4, 63);
            $helper->addAccessRightElementById(4, 64);
            $helper->addAccessRightElementById(4, 65);
            $helper->addAccessRightElementById(4, 66);
            $helper->addAccessRightElementById(4, 67);
            $helper->addAccessRightElementById(4, 68);
            $helper->addAccessRightElementById(4, 69);
            $helper->addAccessRightElementById(4, 70);
            $helper->addAccessRightElementById(4, 71);
            $helper->addAccessRightElementById(4, 72);
            $helper->addAccessRightElementById(4, 73);
            $helper->addAccessRightElementById(4, 74);
            $helper->addAccessRightElementById(4, 75);
            $helper->addAccessRightElementById(4, 76);
            $helper->addAccessRightElementById(4, 236);
            $helper->addAccessRightElementById(4, 237);
            $helper->addAccessRightElementById(4, 238);
            $helper->addAccessRightElementById(4, 239);
            $helper->addAccessRightElementById(4, 240);
            $helper->addAccessRightElementById(4, 241);
            $helper->addAccessRightElementById(4, 242);
            $helper->addAccessRightElementById(4, 243);
            $helper->addAccessRightElementById(4, 244);
            $helper->addAccessRightElementById(4, 245);
            $helper->addAccessRightElementById(4, 246);
            $helper->addAccessRightElementById(4, 247);
            $helper->addAccessRightElementById(4, 248);
            $helper->addAccessRightElementById(4, 249);
            $helper->addAccessRightElementById(4, 250);
            $helper->addAccessRightElementById(4, 251);
            $helper->addAccessRightElementById(4, 252);
            $helper->addAccessRightElementById(4, 253);
            $helper->addAccessRightElementById(4, 254);
            $helper->addAccessRightElementById(4, 255);
            $helper->addAccessRightElementById(4, 256);
            $helper->addAccessRightElementById(4, 257);
            $helper->addAccessRightElementById(4, 258);
            $helper->addAccessRightElementById(4, 259);
            $helper->addAccessRightElementById(4, 260);
            $helper->addAccessRightElementById(4, 261);
            $helper->addAccessRightElementById(4, 262);
            $helper->addAccessRightElementById(4, 263);
            $helper->addAccessRightElementById(4, 264);
            $helper->addAccessRightElementById(4, 265);
            $helper->addAccessRightElementById(4, 266);
            $helper->addAccessRightElementById(4, 267);
            $helper->addAccessRightElementById(4, 268);
            $helper->addAccessRightElementById(4, 269);
            $helper->addAccessRightElementById(4, 270);
            $helper->addAccessRightElementById(4, 271);
            $helper->addAccessRightElementById(4, 272);
            $helper->addAccessRightElementById(4, 273);
            $helper->addAccessRightElementById(4, 274);
            $helper->addAccessRightElementById(4, 275);
            $helper->addAccessRightElementById(4, 276);
            $helper->addAccessRightElementById(4, 277);
            $helper->addAccessRightElementById(4, 278);
            $helper->addAccessRightElementById(4, 279);
            $helper->addAccessRightElementById(4, 280);
            $helper->addAccessRightElementById(4, 281);
            $helper->addAccessRightElementById(4, 282);
            $helper->addAccessRightElementById(4, 283);
            $helper->addAccessRightElementById(4, 284);
            $helper->addAccessRightElementById(4, 285);
            $helper->addAccessRightElementById(4, 286);
            $helper->addAccessRightElementById(4, 287);
            $helper->addAccessRightElementById(4, 288);
            $helper->addAccessRightElementById(4, 289);
            $helper->addAccessRightElementById(4, 290);
            $helper->addAccessRightElementById(4, 291);
            $helper->addAccessRightElementById(4, 292);
            $helper->addAccessRightElementById(4, 294);
            $helper->addAccessRightElementById(4, 295);
            $helper->addAccessRightElementById(4, 296);
            $helper->addAccessRightElementById(4, 297);
            $helper->addAccessRightElementById(4, 298);
            $helper->addAccessRightElementById(4, 299);
            $helper->addAccessRightElementById(4, 300);
            $helper->addAccessRightElementById(4, 301);
            $helper->addAccessRightElementById(4, 302);
            $helper->addAccessRightElementById(4, 303);
            $helper->addAccessRightElementById(4, 304);
            $helper->addAccessRightElementById(4, 305);
            $helper->addAccessRightElementById(4, 306);
            $helper->addAccessRightElementById(4, 307);
            $helper->addAccessRightElementById(4, 308);
            $helper->addAccessRightElementById(4, 309);
            $helper->addAccessRightElementById(4, 310);
            $helper->addAccessRightElementById(4, 311);
            $helper->addAccessRightElementById(4, 312);
            $helper->addAccessRightElementById(4, 313);
            $helper->addAccessRightElementById(4, 314);
            $helper->addAccessRightElementById(4, 315);
            $helper->addAccessRightElementById(4, 316);
            $helper->addAccessRightElementById(4, 317);
            $helper->addAccessRightElementById(4, 318);
            $helper->addAccessRightElementById(4, 319);
            $helper->addAccessRightElementById(4, 320);
            $helper->addAccessRightElementById(4, 321);
            $helper->addAccessRightElementById(4, 322);
            $helper->addAccessRightElementById(4, 323);
            $helper->addAccessRightElementById(4, 324);
            $helper->addAccessRightElementById(4, 325);
            $helper->addAccessRightElementById(4, 326);
            $helper->addAccessRightElementById(4, 327);
            $helper->addAccessRightElementById(4, 328);
            $helper->addAccessRightElementById(4, 329);
            $helper->addAccessRightElementById(4, 330);
            $helper->addAccessRightElementById(4, 331);
            $helper->addAccessRightElementById(4, 332);
            $helper->addAccessRightElementById(4, 333);
            $helper->addAccessRightElementById(4, 334);
            $helper->addAccessRightElementById(4, 335);
            $helper->addAccessRightElementById(4, 336);
            $helper->addAccessRightElementById(4, 337);
            $helper->addAccessRightElementById(4, 338);
            $helper->addAccessRightElementById(4, 339);
            $helper->addAccessRightElementById(4, 340);
            $helper->addAccessRightElementById(4, 341);
            $helper->addAccessRightElementById(4, 342);
            $helper->addAccessRightElementById(4, 343);
            $helper->addAccessRightElementById(4, 344);
            $helper->addAccessRightElementById(4, 345);
            $helper->addAccessRightElementById(4, 346);
            $helper->addAccessRightElementById(4, 347);
            $helper->addAccessRightElementById(4, 348);
            $helper->addAccessRightElementById(4, 349);
            $helper->addAccessRightElementById(4, 350);
            $helper->addAccessRightElementById(4, 351);
            $helper->addAccessRightElementById(4, 352);
            $helper->addAccessRightElementById(4, 353);
            $helper->addAccessRightElementById(4, 354);
            $helper->addAccessRightElementById(4, 355);
            $helper->addAccessRightElementById(4, 356);
            $helper->addAccessRightElementById(4, 357);
            $helper->addAccessRightElementById(4, 358);
            $helper->addAccessRightElementById(4, 359);
            $helper->addAccessRightElementById(4, 360);
            $helper->addAccessRightElementById(4, 361);
            $helper->addAccessRightElementById(4, 362);
            $helper->addAccessRightElementById(4, 363);
            $helper->addAccessRightElementById(4, 364);
            $helper->addAccessRightElementById(4, 365);
            $helper->addAccessRightElementById(4, 366);
            $helper->addAccessRightElementById(4, 367);
            $helper->addAccessRightElementById(4, 368);
            $helper->addAccessRightElementById(4, 369);
            $helper->addAccessRightElementById(4, 370);
            $helper->addAccessRightElementById(4, 371);
            $helper->addAccessRightElementById(4, 372);
            $helper->addAccessRightElementById(4, 373);
            $helper->addAccessRightElementById(4, 374);
            $helper->addAccessRightElementById(4, 375);
            $helper->addAccessRightElementById(4, 376);
            $helper->addAccessRightElementById(4, 377);
            $helper->addAccessRightElementById(4, 378);
            $helper->addAccessRightElementById(4, 379);
            $helper->addAccessRightElementById(4, 380);
            $helper->addAccessRightElementById(4, 381);
            $helper->addAccessRightElementById(4, 382);
            $helper->addAccessRightElementById(4, 383);
            $helper->addAccessRightElementById(4, 384);
            $helper->addAccessRightElementById(4, 385);
            $helper->addAccessRightElementById(4, 386);
            $helper->addAccessRightElementById(4, 387);
            $helper->addAccessRightElementById(4, 388);
            $helper->addAccessRightElementById(4, 389);
            $helper->addAccessRightElementById(4, 390);
            $helper->addAccessRightElementById(4, 391);
            $helper->addAccessRightElementById(4, 392);
            $helper->addAccessRightElementById(4, 393);
            $helper->addAccessRightElementById(4, 394);
            $helper->addAccessRightElementById(4, 395);
            $helper->addAccessRightElementById(4, 396);
            $helper->addAccessRightElementById(4, 397);
            $helper->addAccessRightElementById(4, 398);
            $helper->addAccessRightElementById(4, 399);
            $helper->addAccessRightElementById(4, 400);
            $helper->addAccessRightElementById(4, 401);
            $helper->addAccessRightElementById(4, 402);
            $helper->addAccessRightElementById(4, 403);
            $helper->addAccessRightElementById(4, 404);
            $helper->addAccessRightElementById(4, 405);
            $helper->addAccessRightElementById(4, 406);
            $helper->addAccessRightElementById(4, 407);
            $helper->addAccessRightElementById(4, 408);
            $helper->addAccessRightElementById(4, 409);
            $helper->addAccessRightElementById(4, 410);
            $helper->addAccessRightElementById(4, 411);
            $helper->addAccessRightElementById(4, 412);
            $helper->addAccessRightElementById(4, 413);
            $helper->addAccessRightElementById(4, 414);
            $helper->addAccessRightElementById(4, 415);
            $helper->addAccessRightElementById(4, 416);
            $helper->addAccessRightElementById(4, 417);
            $helper->addAccessRightElementById(4, 418);
            $helper->addAccessRightElementById(4, 419);
            $helper->addAccessRightElementById(4, 420);
            $helper->addAccessRightElementById(4, 421);
            $helper->addAccessRightElementById(4, 422);
            $helper->addAccessRightElementById(4, 423);
            $helper->addAccessRightElementById(4, 424);
            $helper->addAccessRightElementById(4, 425);
            $helper->addAccessRightElementById(4, 426);
            $helper->addAccessRightElementById(4, 427);
            $helper->addAccessRightElementById(4, 428);
            $helper->addAccessRightElementById(4, 429);
            $helper->addAccessRightElementById(4, 430);
            $helper->addAccessRightElementById(4, 431);
            $helper->addAccessRightElementById(4, 432);
            $helper->addAccessRightElementById(4, 433);
            $helper->addAccessRightElementById(4, 434);
            $helper->addAccessRightElementById(4, 435);
            $helper->addAccessRightElementById(4, 436);
            $helper->addAccessRightElementById(4, 437);
            $helper->addAccessRightElementById(4, 438);
            $helper->addAccessRightElementById(4, 439);
            $helper->addAccessRightElementById(4, 440);
            $helper->addAccessRightElementById(4, 441);
            $helper->addAccessRightElementById(4, 442);
            $helper->addAccessRightElementById(4, 443);
            $helper->addAccessRightElementById(4, 444);
            $helper->addAccessRightElementById(4, 445);
            $helper->addAccessRightElementById(4, 446);
            $helper->addAccessRightElementById(4, 447);
            $helper->addAccessRightElementById(4, 448);
            $helper->addAccessRightElementById(4, 449);
            $helper->addAccessRightElementById(4, 450);
            $helper->addAccessRightElementById(4, 451);
            $helper->addAccessRightElementById(4, 452);
            $helper->addAccessRightElementById(4, 453);
            $helper->addAccessRightElementById(4, 454);
            $helper->addAccessRightElementById(4, 455);
            $helper->addAccessRightElementById(4, 456);
            $helper->addAccessRightElementById(4, 457);
            $helper->addAccessRightElementById(4, 458);
            $helper->addAccessRightElementById(4, 459);
            $helper->addAccessRightElementById(4, 460);
            $helper->addAccessRightElementById(4, 461);
            $helper->addAccessRightElementById(4, 462);
            $helper->addAccessRightElementById(4, 463);
            $helper->addAccessRightElementById(4, 464);
            $helper->addAccessRightElementById(4, 465);
            $helper->addAccessRightElementById(4, 466);
            $helper->addAccessRightElementById(4, 467);
            $helper->addAccessRightElementById(4, 468);
            $helper->addAccessRightElementById(4, 469);
            $helper->addAccessRightElementById(4, 470);
            $helper->addAccessRightElementById(4, 471);
            $helper->addAccessRightElementById(4, 472);
            $helper->addAccessRightElementById(4, 473);
            $helper->addAccessRightElementById(4, 474);
            $helper->addAccessRightElementById(4, 475);
            $helper->addAccessRightElementById(4, 476);
            $helper->addAccessRightElementById(4, 477);
            $helper->addAccessRightElementById(4, 478);
            $helper->addAccessRightElementById(4, 479);
            $helper->addAccessRightElementById(4, 480);
            $helper->addAccessRightElementById(4, 481);
            $helper->addAccessRightElementById(4, 482);
            $helper->addAccessRightElementById(4, 483);
            $helper->addAccessRightElementById(4, 484);
            $helper->addAccessRightElementById(4, 485);
            $helper->addAccessRightElementById(4, 486);
            $helper->addAccessRightElementById(4, 487);
            $helper->addAccessRightElementById(4, 488);
            $helper->addAccessRightElementById(4, 489);
            $helper->addAccessRightElementById(4, 490);
            $helper->addAccessRightElementById(4, 491);
            $helper->addAccessRightElementById(4, 492);
            $helper->addAccessRightElementById(4, 493);
            $helper->addAccessRightElementById(4, 494);
            $helper->addAccessRightElementById(4, 495);
            $helper->addAccessRightElementById(4, 496);
            $helper->addAccessRightElementById(4, 497);
            $helper->addAccessRightElementById(4, 498);
            $helper->addAccessRightElementById(4, 499);
            $helper->addAccessRightElementById(4, 500);
            $helper->addAccessRightElementById(4, 501);
            $helper->addAccessRightElementById(4, 502);
            $helper->addAccessRightElementById(4, 503);
            $helper->addAccessRightElementById(4, 504);
            $helper->addAccessRightElementById(4, 505);
            $helper->addAccessRightElementById(4, 506);
            $helper->addAccessRightElementById(4, 507);
            $helper->addAccessRightElementById(4, 508);
            $helper->addAccessRightElementById(4, 509);
            $helper->addAccessRightElementById(4, 510);
            $helper->addAccessRightElementById(4, 511);
            $helper->addAccessRightElementById(4, 512);
            $helper->addAccessRightElementById(4, 513);
            $helper->addAccessRightElementById(4, 514);
            $helper->addAccessRightElementById(4, 515);
            $helper->addAccessRightElementById(4, 516);
            $helper->addAccessRightElementById(4, 517);
            $helper->addAccessRightElementById(4, 518);
            $helper->addAccessRightElementById(4, 519);
            $helper->addAccessRightElementById(4, 520);
            $helper->addAccessRightElementById(4, 521);
            $helper->addAccessRightElementById(4, 522);
            $helper->addAccessRightElementById(4, 523);
            $helper->addAccessRightElementById(4, 524);
            $helper->addAccessRightElementById(4, 525);
            $helper->addAccessRightElementById(4, 526);
            $helper->addAccessRightElementById(4, 527);
            $helper->addAccessRightElementById(4, 528);
            $helper->addAccessRightElementById(4, 529);
            $helper->addAccessRightElementById(4, 530);
            $helper->addAccessRightElementById(4, 531);
            $helper->addAccessRightElementById(4, 532);
            $helper->addAccessRightElementById(4, 533);
            $helper->addAccessRightElementById(4, 534);
            $helper->addAccessRightElementById(4, 535);
            $helper->addAccessRightElementById(4, 536);
            $helper->addAccessRightElementById(4, 537);
            $helper->addAccessRightElementById(4, 538);
            $helper->addAccessRightElementById(4, 539);
            $helper->addAccessRightElementById(4, 540);
            $helper->addAccessRightElementById(4, 541);
            $helper->addAccessRightElementById(4, 542);
            $helper->addAccessRightElementById(4, 543);
            $helper->addAccessRightElementById(4, 544);
            $helper->addAccessRightElementById(4, 545);
            $helper->addAccessRightElementById(4, 546);
            $helper->addAccessRightElementById(4, 547);
            $helper->addAccessRightElementById(4, 548);
            $helper->addAccessRightElementById(4, 549);
            $helper->addAccessRightElementById(4, 550);
            $helper->addAccessRightElementById(4, 551);
            $helper->addAccessRightElementById(4, 552);
            $helper->addAccessRightElementById(4, 553);
            $helper->addAccessRightElementById(4, 554);
            $helper->addAccessRightElementById(4, 555);
            $helper->addAccessRightElementById(4, 556);
            $helper->addAccessRightElementById(4, 557);
            $helper->addAccessRightElementById(4, 558);
            $helper->addAccessRightElementById(4, 559);
            $helper->addAccessRightElementById(4, 560);
            $helper->addAccessRightElementById(4, 561);
            $helper->addAccessRightElementById(4, 562);
            $helper->addAccessRightElementById(4, 563);
            $helper->addAccessRightElementById(4, 564);
            $helper->addAccessRightElementById(4, 565);
            $helper->addAccessRightElementById(4, 566);
            $helper->addAccessRightElementById(4, 567);
            $helper->addAccessRightElementById(4, 568);
            $helper->addAccessRightElementById(4, 569);
            $helper->addAccessRightElementById(4, 570);
            $helper->addAccessRightElementById(4, 571);
            $helper->addAccessRightElementById(4, 572);
            $helper->addAccessRightElementById(4, 573);
            $helper->addAccessRightElementById(4, 574);
            $helper->addAccessRightElementById(4, 575);
            $helper->addAccessRightElementById(4, 576);
            $helper->addAccessRightElementById(4, 577);
            $helper->addAccessRightElementById(4, 578);
            $helper->addAccessRightElementById(4, 579);
            $helper->addAccessRightElementById(4, 580);
            $helper->addAccessRightElementById(4, 581);
            $helper->addAccessRightElementById(4, 582);
            $helper->addAccessRightElementById(4, 583);
            $helper->addAccessRightElementById(4, 584);
            $helper->addAccessRightElementById(4, 585);
            $helper->addAccessRightElementById(4, 586);
            $helper->addAccessRightElementById(4, 587);
            $helper->addAccessRightElementById(4, 588);
            $helper->addAccessRightElementById(4, 589);
            $helper->addAccessRightElementById(4, 590);
            $helper->addAccessRightElementById(4, 591);
            $helper->addAccessRightElementById(4, 592);
            $helper->addAccessRightElementById(4, 593);
            $helper->addAccessRightElementById(4, 594);
            $helper->addAccessRightElementById(4, 595);
            $helper->addAccessRightElementById(4, 596);
            $helper->addAccessRightElementById(4, 597);
            $helper->addAccessRightElementById(4, 598);
            $helper->addAccessRightElementById(4, 599);
            $helper->addAccessRightElementById(4, 600);
            $helper->addAccessRightElementById(4, 601);
            $helper->addAccessRightElementById(4, 602);
            $helper->addAccessRightElementById(4, 603);
            $helper->addAccessRightElementById(4, 604);
            $helper->addAccessRightElementById(4, 605);
            $helper->addAccessRightElementById(4, 606);
            $helper->addAccessRightElementById(4, 607);
            $helper->addAccessRightElementById(4, 608);
            $helper->addAccessRightElementById(4, 609);
            $helper->addAccessRightElementById(4, 610);
            $helper->addAccessRightElementById(4, 611);
            $helper->addAccessRightElementById(4, 612);
            $helper->addAccessRightElementById(4, 613);
            $helper->addAccessRightElementById(4, 614);
            $helper->addAccessRightElementById(4, 615);
            $helper->addAccessRightElementById(4, 616);
            $helper->addAccessRightElementById(4, 617);
            $helper->addAccessRightElementById(4, 618);
            $helper->addAccessRightElementById(4, 619);
            $helper->addAccessRightElementById(4, 620);
            $helper->addAccessRightElementById(4, 621);
            $helper->addAccessRightElementById(4, 622);
            $helper->addAccessRightElementById(4, 623);
            $helper->addAccessRightElementById(4, 624);
            $helper->addAccessRightElementById(4, 625);
            $helper->addAccessRightElementById(4, 626);
            $helper->addAccessRightElementById(4, 627);
            $helper->addAccessRightElementById(4, 628);
            $helper->addAccessRightElementById(4, 629);
            $helper->addAccessRightElementById(4, 630);
            $helper->addAccessRightElementById(4, 631);
            $helper->addAccessRightElementById(4, 632);
            $helper->addAccessRightElementById(4, 633);
            $helper->addAccessRightElementById(4, 634);
            $helper->addAccessRightElementById(4, 635);
            $helper->addAccessRightElementById(4, 636);
            $helper->addAccessRightElementById(4, 637);
            $helper->addAccessRightElementById(4, 638);
            $helper->addAccessRightElementById(4, 639);
            $helper->addAccessRightElementById(4, 640);
            $helper->addAccessRightElementById(4, 641);
            $helper->addAccessRightElementById(4, 642);
            $helper->addAccessRightElementById(4, 643);
            $helper->addAccessRightElementById(4, 644);
            $helper->addAccessRightElementById(4, 645);
            $helper->addAccessRightElementById(4, 646);
            $helper->addAccessRightElementById(4, 647);
            $helper->addAccessRightElementById(4, 648);
            $helper->addAccessRightElementById(4, 649);
            $helper->addAccessRightElementById(4, 650);
            $helper->addAccessRightElementById(4, 651);
            $helper->addAccessRightElementById(4, 652);
            $helper->addAccessRightElementById(4, 653);
            $helper->addAccessRightElementById(4, 654);
            $helper->addAccessRightElementById(4, 655);
            $helper->addAccessRightElementById(4, 656);
            $helper->addAccessRightElementById(4, 657);
            $helper->addAccessRightElementById(4, 658);
            $helper->addAccessRightElementById(4, 659);
            $helper->addAccessRightElementById(4, 660);
            $helper->addAccessRightElementById(4, 661);
            $helper->addAccessRightElementById(4, 662);
            $helper->addAccessRightElementById(4, 663);
            $helper->addAccessRightElementById(4, 664);
            $helper->addAccessRightElementById(4, 665);
            $helper->addAccessRightElementById(4, 666);
            $helper->addAccessRightElementById(4, 667);
            $helper->addAccessRightElementById(4, 668);
            $helper->addAccessRightElementById(4, 669);
            $helper->addAccessRightElementById(4, 670);
            $helper->addAccessRightElementById(4, 671);
            $helper->addAccessRightElementById(4, 672);
            $helper->addAccessRightElementById(4, 673);
            $helper->addAccessRightElementById(4, 674);
            $helper->addAccessRightElementById(4, 675);
            $helper->addAccessRightElementById(4, 676);
            $helper->addAccessRightElementById(4, 677);
            $helper->addAccessRightElementById(4, 678);
            $helper->addAccessRightElementById(4, 679);
            $helper->addAccessRightElementById(4, 680);
            $helper->addAccessRightElementById(4, 681);
            $helper->addAccessRightElementById(4, 682);
            $helper->addAccessRightElementById(4, 683);
            $helper->addAccessRightElementById(4, 684);
            $helper->addAccessRightElementById(4, 685);
            $helper->addAccessRightElementById(4, 686);
            $helper->addAccessRightElementById(4, 687);
            $helper->addAccessRightElementById(4, 688);
            $helper->addAccessRightElementById(4, 689);
            $helper->addAccessRightElementById(4, 690);
            $helper->addAccessRightElementById(4, 691);
            $helper->addAccessRightElementById(4, 692);
            $helper->addAccessRightElementById(4, 693);
            $helper->addAccessRightElementById(4, 694);
            $helper->addAccessRightElementById(4, 695);
            $helper->addAccessRightElementById(4, 696);
            $helper->addAccessRightElementById(4, 697);
            $helper->addAccessRightElementById(4, 698);
            $helper->addAccessRightElementById(4, 699);
            $helper->addAccessRightElementById(4, 700);
            $helper->addAccessRightElementById(4, 701);
            $helper->addAccessRightElementById(4, 702);
            $helper->addAccessRightElementById(4, 703);
            $helper->addAccessRightElementById(4, 706);
            $helper->addAccessRightElementById(4, 715);
            $helper->addAccessRightElementById(4, 718);
            $helper->addAccessRightElementById(4, 719);
            $helper->addAccessRightElementById(4, 720);
            $helper->addAccessRightElementById(4, 721);
            $helper->addAccessRightElementById(4, 722);
            $helper->addAccessRightElementById(4, 723);
            $helper->addAccessRightElementById(4, 724);
            $helper->addAccessRightElementById(4, 725);
            $helper->addAccessRightElementById(4, 726);
            $helper->addAccessRightElementById(4, 727);
            $helper->addAccessRightElementById(4, 728);
            $helper->addAccessRightElementById(4, 729);
            $helper->addAccessRightElementById(4, 734);
            $helper->addAccessRightElementById(4, 735);
            $helper->addAccessRightElementById(4, 736);
            $helper->addAccessRightElementById(4, 737);
            $helper->addAccessRightElementById(4, 738);
            $helper->addAccessRightElementById(4, 740);
            $helper->addAccessRightElementById(4, 741);
            $helper->addAccessRightElementById(4, 742);
            $helper->addAccessRightElementById(4, 743);
            $helper->addAccessRightElementById(4, 744);
            $helper->addAccessRightElementById(4, 745);
            $helper->addAccessRightElementById(4, 746);
            $helper->addAccessRightElementById(4, 747);
            $helper->addAccessRightElementById(4, 748);
            $helper->addAccessRightElementById(4, 749);
            $helper->addAccessRightElementById(4, 750);
            $helper->addAccessRightElementById(4, 751);
            $helper->addAccessRightElementById(4, 752);
            $helper->addAccessRightElementById(4, 753);
            $helper->addAccessRightElementById(4, 754);
            $helper->addAccessRightElementById(4, 755);
            $helper->addAccessRightElementById(4, 756);
            $helper->addAccessRightElementById(4, 757);
            $helper->addAccessRightElementById(4, 758);
            $helper->addAccessRightElementById(4, 759);
            $helper->addAccessRightElementById(4, 761);
            $helper->addAccessRightElementById(4, 762);
            $helper->addAccessRightElementById(4, 763);
            $helper->addAccessRightElementById(4, 764);
            $helper->addAccessRightElementById(4, 765);
            $helper->addAccessRightElementById(4, 766);
            $helper->addAccessRightElementById(4, 767);
            $helper->addAccessRightElementById(4, 769);
            $helper->addAccessRightElementById(4, 770);
            $helper->addAccessRightElementById(4, 771);
            $helper->addAccessRightElementById(4, 775);
            $helper->addAccessRightElementById(4, 777);
            $helper->addAccessRightElementById(4, 780);
            $helper->addAccessRightElementById(4, 782);
            $helper->addAccessRightElementById(4, 783);
            $helper->addAccessRightElementById(4, 784);
            $helper->addAccessRightElementById(4, 785);
            $helper->addAccessRightElementById(4, 786);
            $helper->addAccessRightElementById(4, 787);
            $helper->addAccessRightElementById(4, 788);
            $helper->addAccessRightElementById(4, 789);
            $helper->addAccessRightElementById(4, 790);
            $helper->addAccessRightElementById(4, 791);
            $helper->addAccessRightElementById(4, 792);
            $helper->addAccessRightElementById(4, 793);
            $helper->addAccessRightElementById(4, 795);
            $helper->addAccessRightElementById(4, 796);
            $helper->addAccessRightElementById(4, 797);
            $helper->addAccessRightElementById(4, 798);
            $helper->addAccessRightElementById(4, 799);
            $helper->addAccessRightElementById(4, 800);
            $helper->addAccessRightElementById(4, 801);
            $helper->addAccessRightElementById(4, 802);
            $helper->addAccessRightElementById(4, 803);
            $helper->addAccessRightElementById(4, 804);
            $helper->addAccessRightElementById(4, 805);
            $helper->addAccessRightElementById(4, 806);
            $helper->addAccessRightElementById(4, 807);
            $helper->addAccessRightElementById(4, 808);
            $helper->addAccessRightElementById(4, 809);
            $helper->addAccessRightElementById(4, 810);
            $helper->addAccessRightElementById(4, 811);
            $helper->addAccessRightElementById(4, 812);
            $helper->addAccessRightElementById(4, 813);
            $helper->addAccessRightElementById(4, 814);
            $helper->addAccessRightElementById(4, 815);
            $helper->addAccessRightElementById(4, 816);
            $helper->addAccessRightElementById(4, 817);
            $helper->addAccessRightElementById(4, 818);
            $helper->addAccessRightElementById(4, 819);
            $helper->addAccessRightElementById(4, 820);
            $helper->addAccessRightElementById(4, 821);
            $helper->addAccessRightElementById(4, 822);
            $helper->addAccessRightElementById(4, 823);
            $helper->addAccessRightElementById(4, 824);
            $helper->addAccessRightElementById(4, 825);
            $helper->addAccessRightElementById(4, 826);
            $helper->addAccessRightElementById(4, 827);
            $helper->addAccessRightElementById(4, 828);
            $helper->addAccessRightElementById(4, 829);
            $helper->addAccessRightElementById(4, 830);
            $helper->addAccessRightElementById(4, 831);
            $helper->addAccessRightElementById(4, 832);
            $helper->addAccessRightElementById(4, 833);
            $helper->addAccessRightElementById(4, 834);
            $helper->addAccessRightElementById(4, 835);
            $helper->addAccessRightElementById(4, 836);
            $helper->addAccessRightElementById(4, 837);
            $helper->addAccessRightElementById(4, 838);
            $helper->addAccessRightElementById(4, 839);
            $helper->addAccessRightElementById(4, 840);
            $helper->addAccessRightElementById(4, 841);
            $helper->addAccessRightElementById(4, 842);
            $helper->addAccessRightElementById(4, 843);
            $helper->addAccessRightElementById(4, 844);
            $helper->addAccessRightElementById(4, 845);
            $helper->addAccessRightElementById(4, 846);
            $helper->addAccessRightElementById(4, 847);
            $helper->addAccessRightElementById(4, 848);
            $helper->addAccessRightElementById(4, 849);
            $helper->addAccessRightElementById(4, 850);
            $helper->addAccessRightElementById(4, 851);
            $helper->addAccessRightElementById(4, 852);
            $helper->addAccessRightElementById(4, 853);
            $helper->addAccessRightElementById(4, 854);
            $helper->addAccessRightElementById(4, 855);
            $helper->addAccessRightElementById(4, 856);
            $helper->addAccessRightElementById(4, 857);
            $helper->addAccessRightElementById(4, 858);
            $helper->addAccessRightElementById(4, 859);
            $helper->addAccessRightElementById(4, 861);
            $helper->addAccessRightElementById(4, 862);
            $helper->addAccessRightElementById(4, 863);
            $helper->addAccessRightElementById(4, 864);
            $helper->addAccessRightElementById(4, 865);
            $helper->addAccessRightElementById(4, 866);
            $helper->addAccessRightElementById(4, 867);
            $helper->addAccessRightElementById(4, 868);
            $helper->addAccessRightElementById(4, 869);
            $helper->addAccessRightElementById(4, 870);
            $helper->addAccessRightElementById(4, 871);
            $helper->addAccessRightElementById(4, 872);
            $helper->addAccessRightElementById(4, 873);
            $helper->addAccessRightElementById(4, 874);
            $helper->addAccessRightElementById(4, 875);
            $helper->addAccessRightElementById(4, 876);
            $helper->addAccessRightElementById(4, 877);
            $helper->addAccessRightElementById(4, 878);
            $helper->addAccessRightElementById(4, 879);
            $helper->addAccessRightElementById(4, 880);
            $helper->addAccessRightElementById(4, 881);
            $helper->addAccessRightElementById(4, 882);
            $helper->addAccessRightElementById(4, 883);
            $helper->addAccessRightElementById(4, 884);
            $helper->addAccessRightElementById(4, 885);
            $helper->addAccessRightElementById(4, 886);
            $helper->addAccessRightElementById(4, 887);
            $helper->addAccessRightElementById(4, 888);
            $helper->addAccessRightElementById(4, 889);
            $helper->addAccessRightElementById(4, 890);
            $helper->addAccessRightElementById(4, 891);
            $helper->addAccessRightElementById(4, 892);
            $helper->addAccessRightElementById(4, 893);
            $helper->addAccessRightElementById(4, 894);
            $helper->addAccessRightElementById(4, 895);
            $helper->addAccessRightElementById(4, 896);
            $helper->addAccessRightElementById(4, 897);
            $helper->addAccessRightElementById(4, 898);
            $helper->addAccessRightElementById(4, 899);
            $helper->addAccessRightElementById(4, 900);
            $helper->addAccessRightElementById(4, 901);
            $helper->addAccessRightElementById(4, 902);
            $helper->addAccessRightElementById(4, 903);
            $helper->addAccessRightElementById(4, 904);
            $helper->addAccessRightElementById(4, 905);
            $helper->addAccessRightElementById(4, 906);
            $helper->addAccessRightElementById(4, 907);
            $helper->addAccessRightElementById(4, 908);
            $helper->addAccessRightElementById(4, 909);
            $helper->addAccessRightElementById(4, 910);
            $helper->addAccessRightElementById(4, 911);
            $helper->addAccessRightElementById(4, 912);
            $helper->addAccessRightElementById(4, 913);
            $helper->addAccessRightElementById(4, 914);
            $helper->addAccessRightElementById(4, 915);
            $helper->addAccessRightElementById(4, 916);
            $helper->addAccessRightElementById(4, 917);
            $helper->addAccessRightElementById(4, 918);
            $helper->addAccessRightElementById(4, 919);
            $helper->addAccessRightElementById(4, 920);
            $helper->addAccessRightElementById(4, 921);
            $helper->addAccessRightElementById(4, 922);
            $helper->addAccessRightElementById(4, 923);
            $helper->addAccessRightElementById(4, 924);
            $helper->addAccessRightElementById(4, 925);
            $helper->addAccessRightElementById(4, 926);
            $helper->addAccessRightElementById(4, 927);
            $helper->addAccessRightElementById(4, 928);
            $helper->addAccessRightElementById(4, 929);
            $helper->addAccessRightElementById(4, 930);
            $helper->addAccessRightElementById(4, 765);
            $helper->addAccessRightElementById(4, 766);
            $helper->addAccessRightElementById(4, 767);
            $helper->addAccessRightElementById(4, 761);
            $helper->addAccessRightElementById(4, 742);
            $helper->addAccessRightElementById(4, 951);
            $helper->addAccessRightElementById(4, 952);
            $helper->addAccessRightElementById(4, 78);
            $helper->addAccessRightElementById(4, 830);
            $helper->addAccessRightElementById(4, 832);
            $helper->addAccessRightElementById(4, 833);
            $helper->addAccessRightElementById(4, 834);
            $helper->addAccessRightElementById(4, 835);
            $helper->addAccessRightElementById(4, 836);
            $helper->addAccessRightElementById(4, 837);
            $helper->addAccessRightElementById(4, 838);
            $helper->addAccessRightElementById(4, 839);
            $helper->addAccessRightElementById(4, 840);
            $helper->addAccessRightElementById(4, 841);
            $helper->addAccessRightElementById(4, 842);
            $helper->addAccessRightElementById(4, 843);
            $helper->addAccessRightElementById(5, 959);
            $helper->addAccessRightElementById(6, 960);
            $helper->addAccessRightElementById(7, 961);
            $helper->addAccessRightElementById(8, 962);
            $helper->addAccessRightElementById(9, 963);
            $helper->addAccessRightElementById(10, 967);
            $helper->addAccessRightElementById(10, 968);
            $helper->addAccessRightElementById(11, 964);
            $helper->addAccessRightElementById(12, 969);
            $helper->addAccessRightElementById(12, 970);
            $helper->addAccessRightElementById(14, 966);
            $helper->addAccessRightElementById(15, 974);
            $helper->addAccessRightElementById(16, 975);
            $helper->addAccessRightElementById(17, 982);
            $helper->addAccessRightElementById(18, 983);
            $helper->addAccessRightElementById(19, 988);
            $helper->addAccessRightElementById(20, 989);
            $helper->addAccessRightElementById(21, 990);
            $helper->addAccessRightElementById(22, 994);
            $helper->addAccessRightElementById(23, 1020);
            $helper->addAccessRightElementById(24, 1024);
            $helper->addAccessRightElementById(25, 1028);
            $helper->addAccessRightElementById(26, 1029);
            $helper->addAccessRightElementById(27, 965);
            $helper->addAccessRightElementById(27, 954);
            $helper->addAccessRightElementById(27, 955);
            $helper->addAccessRightElementById(27, 956);
            $helper->addAccessRightElementById(27, 957);
            $helper->addAccessRightElementById(5, 241);
            $helper->addAccessRightElementById(5, 736);
            $helper->addAccessRightElementById(28, 971);
            $helper->addAccessRightElementById(29, 984);
            $helper->addAccessRightElementById(30, 1030);
            $helper->addAccessRightElementById(31, 1041);
            $helper->addAccessRightElementById(31, 972);
            $helper->addAccessRightElementById(32, 1002);
            $helper->addAccessRightElementById(32, 1011);
            $helper->addAccessRightElementById(32, 1012);
            $helper->addAccessRightElementById(33, 1004);
            $helper->addAccessRightElementById(33, 1010);
            $helper->addAccessRightElementById(33, 1013);
            $helper->addAccessRightElementById(33, 1014);
            $helper->addAccessRightElementById(33, 1015);
            $helper->addAccessRightElementById(33, 1042);
            $helper->addAccessRightElementById(34, 1007);
            $helper->addAccessRightElementById(34, 1008);
            $helper->addAccessRightElementById(34, 1009);
            $helper->addAccessRightElementById(35, 1003);
            $helper->addAccessRightElementById(35, 1005);
            $helper->addAccessRightElementById(35, 1006);
            $helper->addAccessRightElementById(36, 998);
            $helper->addAccessRightElementById(36, 1000);
            $helper->addAccessRightElementById(36, 1001);
            $helper->addAccessRightElementById(37, 999);
            $helper->addAccessRightElementById(4, 1044);
            $helper->addAccessRightElementById(38, 1068);
            $helper->addAccessRightElementById(39, 1071);
            $helper->addAccessRightElementById(40, 1072);
            $helper->addAccessRightElementById(41, 1074);
            $helper->addAccessRightElementById(42, 1079);
            $helper->addAccessRightElementById(43, 1080);
            $helper->addAccessRightElementById(44, 1075);
            $helper->addAccessRightElementById(45, 1076);
            $helper->addAccessRightElementById(46, 1077);
            $helper->addAccessRightElementById(47, 1078);
            $helper->addAccessRightElementById(48, 1082);
            $helper->addAccessRightElementById(49, 1083);
            $helper->addAccessRightElementById(50, 1088);
            $helper->addAccessRightElementById(51, 1090);
            $helper->addAccessRightElementById(52, 1091);
            $helper->addAccessRightElementById(53, 1092);
            $helper->addAccessRightElementById(54, 1063);
            $helper->addAccessRightElementById(54, 1064);
            $helper->addAccessRightElementById(54, 1065);
            $helper->addAccessRightElementById(54, 1066);
            $helper->addAccessRightElementById(38, 736);
            $helper->addAccessRightElementById(38, 241);
            $helper->addAccessRightElementById(38, 736);
        }

        if (version_compare($context->getVersion(), '1.0.7.2.2', '<')) {
            $this->commonHelperSetup->addAccessElement('Magento_Customer', 'Account', 'createPassword', '', 'Access', 1, 1);
        }

        if (version_compare($context->getVersion(), '1.0.7.2.3', '<')) {
            $this->version_1_0_7_2_3($customerSetup);
        }

        if (version_compare($context->getVersion(), '1.0.7.2.4', '<')) {
            $this->version_1_0_7_2_4($setup);
        }

        if (version_compare($context->getVersion(), '1.0.7.2.5', '<')) {
            $this->version_1_0_7_2_5($setup);
        }

        if (version_compare($context->getVersion(), '1.0.7.2.6', '<')) {
            $this->version_1_0_7_2_6($setup);
        }

        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $this->version_1_0_8();
        }

        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            $this->version_1_0_9($setup);
        }
    }


    /**
     * Add customer eav attribute "ecc_allow_shipping_address_create" and "ecc_allow_billing_address_create".
     *
     * @param CustomerSetup $customerSetup Customer setup.
     *
     * @return void
     * @throws Zend_Validate_Exception Throw exception.
     *
     * @throws LocalizedException Throw exception.
     */
    private function version_1_0_7_2_3(CustomerSetup $customerSetup)
    {
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'ecc_allow_shipping_address_create',
            [
                'group' => 'General',
                'label' => 'Allow Shipping Address Creation',
                'type' => 'varchar',
                'input' => 'select',
                'visible' => false,
                'required' => false,
                'user_defined' => 1,
                'position' => 150,
                'source' => 'Epicor\BranchPickup\Model\Eav\Attribute\Data\Branchoptions',
                'default' => '2',
                'system' => false,
            ]
        );

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'ecc_allow_billing_address_create',
            [
                'group' => 'General',
                'label' => 'Allow Billing Address Creation',
                'type' => 'varchar',
                'input' => 'select',
                'visible' => false,
                'required' => false,
                'user_defined' => 1,
                'position' => 160,
                'source' => 'Epicor\BranchPickup\Model\Eav\Attribute\Data\Branchoptions',
                'default' => '2',
                'system' => false,
            ]
        );

        $attributes = [
            'ecc_allow_shipping_address_create',
            'ecc_allow_billing_address_create',
        ];

        foreach ($attributes as $attributeCode) {
            $attr = $customerSetup->getEavConfig()->getAttribute('customer', $attributeCode);
            $attr->setData('used_in_forms', ['adminhtml_customer']);
            $attr->save();
        }

    }//end version_1_0_7_2_3()


    /**
     * Update version 1.0.7.2.4
     *
     * @param ModuleDataSetupInterface $setup Setup data interface.
     *
     * @return void
     *
     * @throws \Zend_Db_Exception Throw exception.
     * @throws \Zend_Db_Statement_Exception Throw exception.
     */
    private function version_1_0_7_2_4(ModuleDataSetupInterface $setup)
    {
        $this->updateAddressConfigs($setup);
        $this->updateAddressCreateValues($setup);

    }//end version_1_0_7_2_4()


    /**
     * Update CUS config values.
     *
     * @param ModuleDataSetupInterface $setup Setup data interface.
     *
     * @return void
     *
     * @throws \Zend_Db_Exception Throw exception.
     * @throws \Zend_Db_Statement_Exception Throw exception.
     */
    private function updateAddressConfigs(ModuleDataSetupInterface $setup)
    {
        $configPaths = $this->getConfigPaths();
        $writeConnection = $setup->getConnection('core_write');
        foreach ($configPaths as $type => $value) {
            $newVal = $writeConnection->query(
                'SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0
                 AND path = \'' . $type . '\''
            );
            $oldVal = $writeConnection->query(
                'SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0
                 AND path = \'' . $value . '\''
            );
            $newInfo = $newVal->fetch();
            $oldInfo = $oldVal->fetch();
            if ($newInfo !== false) {
                continue;
            }

            if ($oldInfo) {
                $data = [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => $type,
                    'value' => $oldInfo['value'],
                ];
                $writeConnection->insertOnDuplicate(
                    $setup->getTable('core_config_data'),
                    $data,
                    ['value']
                );
            }
        }//end foreach

    }//end updateAddressConfigs()


    /**
     * Update address create values.
     *
     * @param ModuleDataSetupInterface $setup Setup data interface.
     *
     * @return void
     *
     * @throws \Zend_Db_Exception Throw exception.
     * @throws \Zend_Db_Statement_Exception Throw exception.
     */
    private function updateAddressCreateValues(ModuleDataSetupInterface $setup)
    {
        $writeConnection = $setup->getConnection('core_write');
        $queryShipping = $writeConnection->query(
            'update ecc_erp_account set shipping_address_allowed = custom_address_allowed
                  where custom_address_allowed IS NOT NULL'
        );
        $queryBilling = $writeConnection->query(
            'update ecc_erp_account set billing_address_allowed = custom_address_allowed
                  where custom_address_allowed IS NOT NULL'
        );
        $queryShipping->execute();
        $queryBilling->execute();

    }//end updateAddressCreateValues()


    /**
     * Get Config paths.
     *
     * @return array
     */
    private function getConfigPaths()
    {
        $types = [
            self::XML_PATH_SHIPPING_CREATE => self::XML_PATH_ADDRESS_CREATE,
            self::XML_PATH_BILLING_CREATE => self::XML_PATH_ADDRESS_CREATE,
        ];
        return $types;

    }//end getConfigPaths()

    /**
     * Update version 1.0.7.2.5.
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup Setup.
     *
     * @return void Void.
     *
     * @throws \Zend_Db_Adapter_Exception Exception.
     * @throws \Zend_Db_Exception Exception.
     * @throws \Zend_Db_Statement_Exception Exception.
     *
     * @throws \Magento\Framework\Exception\LocalizedException LocalizedException.
     */
    private function version_1_0_7_2_5(ModuleDataSetupInterface $setup)
    {
        $writeConnection = $setup->getConnection('core_write');
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql */
        $customRoles = $writeConnection->query(
            'SELECT role_id FROM authorization_rule WHERE resource_id=\'Epicor_Common::mapping\' AND permission = \'allow\''
        );
        $customRoleInfo = $customRoles->fetchAll();

        if (is_array($customRoleInfo) && count($customRoleInfo) > 0) {
            $resourceIds = [
                'Epicor_Common::mapping_customfields', 'Epicor_Common::epicorcommon_mapping_pac',
                'Epicor_Common::epicorcommon_mapping_warranty', 'Epicor_Common::mapping_shipping_status',
                'Epicor_Common::mapping_products',
                'Epicor_Customerconnect::epicorcommon_mapping_miscellaneouscharges',
                'Epicor_Common::mapping_claim_status',
                'Epicor_Common::epicorcommon_data_mapping'];
            foreach ($customRoleInfo as $k => $id) {
                foreach ($resourceIds as $resourceId) {
                    $data [] = [
                        'role_id' => $id['role_id'],
                        'resource_id' => $resourceId,
                        'privileges' => NULL,
                        'permission' => 'allow',
                    ];
                }

            }
            $writeConnection->insertMultiple('authorization_rule', $data);
        }

        $customRolesForNewPages = $writeConnection->query(
            'SELECT role_id FROM authorization_rule WHERE resource_id=\'Epicor_Common::manage\' AND permission = \'allow\''
        );
        $customRolesForNewPagesInfo = $customRolesForNewPages->fetchAll();

        if (is_array($customRolesForNewPagesInfo) && count($customRolesForNewPagesInfo) > 0) {
            $resourceIds = [
                'Epicor_Dealerconnect::groups', 'Epicor_AccessRight::roles', 'Epicor_Comm::access_control_config'];
            foreach ($customRolesForNewPagesInfo as $k => $id) {
                foreach ($resourceIds as $resourceId) {
                    $dataManager [] = [
                        'role_id' => $id['role_id'],
                        'resource_id' => $resourceId,
                        'privileges' => NULL,
                        'permission' => 'deny',
                    ];
                }

            }
            $writeConnection->insertMultiple('authorization_rule', $dataManager);
        }

        $customRolesForNew = $writeConnection->query(
            'SELECT role_id FROM authorization_rule WHERE resource_id=\'Magento_Sales::sales_operation\' AND permission = \'allow\''
        );
        $customRolesForNewInfo = $customRolesForNew->fetchAll();

        if (is_array($customRolesForNewInfo) && count($customRolesForNewInfo) > 0) {
            $resourceIds = ['Epicor_Customerconnect::arpayments'];
            foreach ($customRolesForNewInfo as $k => $id) {
                foreach ($resourceIds as $resourceId) {
                    $dataARPay [] = [
                        'role_id' => $id['role_id'],
                        'resource_id' => $resourceId,
                        'privileges' => NULL,
                        'permission' => 'deny',
                    ];
                }

            }
            $writeConnection->insertMultiple('authorization_rule', $dataARPay);
        }

    }//end version_1_0_7_2_5()


    /**
     * Update New limit configurations with existing value
     * @param ModuleDataSetupInterface $setup
     * @throws \Zend_Db_Statement_Exception
     */
    private function version_1_0_7_2_6(ModuleDataSetupInterface $setup)
    {
        $writeConnection = $setup->getConnection('core_write');
        $nameLimit = $writeConnection->query(
            'SELECT value FROM core_config_data WHERE scope=\'default\' AND scope_id = 0
                 AND path = \'' . self::XML_PATH_LIMIT_NAME_LENGTH . '\''
        );

        $limit = $nameLimit->fetch();
        if ($limit !== false) {
            $newConfigs = [
                self::XML_PATH_LIMIT_LASTNAME_LENGTH,
                self::XML_PATH_LIMIT_COMPANY_LENGTH
            ];
            foreach ($newConfigs as $newConf) {
                $data = [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => $newConf,
                    'value' => $limit['value'],
                ];
                $writeConnection->insertOnDuplicate(
                    $setup->getTable('core_config_data'),
                    $data
                );
            }
        }
    }

    /**
     * Modifying Auto Sync Configuration
     * @param ModuleDataSetupInterface $setup
     * @throws \Zend_Db_Statement_Exception
     */
    private function version_1_0_8()
    {
        $enabled = $this->scopeConfig->getValue(
            'epicor_comm_enabled_messages/syn_request/autosync_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($enabled) {
            $simple = $this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/autosync_simple_messages', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $advanced = $this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/autosync_advanced_messages', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $language = $this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/autosync_language', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $freq_value = $this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/autosync_frequency_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $freq_unit = $this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/autosync_frequency_unit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $syncStores = $this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/autosync_stores', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $nextDateInSynConfig = $this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/autosync_next_date_from_in_syn', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $nextRunDateConfig = $this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/autosync_start_date', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $startDateConfig = $this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/autosync_next_rundate', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $messagesArray = $this->getAdvancedValue($advanced, $simple);

            $syncTypes = array_keys($this->messagesList);
            $syncTypesMessages = $this->messagesList;
            $finalMessagesArray = [];
            foreach ($syncTypesMessages as $type => $messages) {
                foreach ($messagesArray as $value) {
                    if (in_array($value, $messages)) {
                        $finalMessagesArray[$type][] = $value;
                    }
                }
            }
            foreach ($syncTypes as $syncType) {
                if (isset($finalMessagesArray[$syncType])) {
                    $finalAdvanced = implode(',', $finalMessagesArray[$syncType]);
                    $prefixpath = 'epicor_comm_enabled_messages/syn_request/';
                    $this->resourceConfig->saveConfig($prefixpath . $syncType . '_autosync_enabled', 1, 'default', 0);
                    $this->resourceConfig->saveConfig($prefixpath . $syncType . '_autosync_advanced_messages', $finalAdvanced, 'default', 0);
                    $this->resourceConfig->saveConfig($prefixpath . $syncType . '_autosync_language', $language, 'default', 0);
                    $this->resourceConfig->saveConfig($prefixpath . $syncType . '_autosync_frequency_value', $freq_value, 'default', 0);
                    $this->resourceConfig->saveConfig($prefixpath . $syncType . '_autosync_frequency_unit', $freq_unit, 'default', 0);
                    $this->resourceConfig->saveConfig($prefixpath . $syncType . '_autosync_stores', $syncStores, 'default', 0);
                    $this->resourceConfig->saveConfig($prefixpath . $syncType . '_autosync_next_date_from_in_syn', $nextDateInSynConfig, 'default', 0);
                    $this->resourceConfig->saveConfig($prefixpath . $syncType . '_autosync_start_date', $nextRunDateConfig, 'default', 0);
                    $this->resourceConfig->saveConfig($prefixpath . $syncType . '_autosync_next_rundate', $startDateConfig, 'default', 0);

                }
            }
            $this->cache->clean(array('CONFIG'));
        }
    }

    /**
     * Return Advanced Value
     * @param array $advanced
     * @param array $simple
     * @return  array
     */

    private function getAdvancedValue($advanced, $simple)
    {
        $messagesArray = [];
        if ($advanced) {       // if advanced messages exist the advanced option has been selected - ignore simple
            $messagesArray = explode(',', $advanced);
        } else {
            $messageLabels = explode(',', $simple);      // need to determine the codes from the name
            foreach ($this->simpleMessages as $msg) {
                if (in_array($msg['label'], $messageLabels)) {
                    $messagesArray = array_merge($messagesArray, $msg['value']);
                }
            }
        }
        return $messagesArray;
    }

    /**
     * Captcha - update new form with making enable captcha
     *
     * @param ModuleDataSetupInterface $setup
     * @throws \Zend_Db_Statement_Exception
     */
    private function version_1_0_9(ModuleDataSetupInterface $setup)
    {
        $defaultFormSelected = [
            "user_create",
            "user_forgotpassword",
            "user_login",
            "user_edit",
            "co-payment-form",
            "b2b_create",
        ];

        $writeConnection = $setup->getConnection('core_write');
        $captchaForms = $writeConnection->query(
            'SELECT * FROM core_config_data WHERE path = \''.self::XML_PATH_CUSTOMER_CAPTCHA_FORMS.'\''
        );

        $forms = $captchaForms->fetchAll();
        if ($forms) {
            foreach ($forms as $form) {
                $newValue = implode(",",
                    array_unique(
                        array_merge(
                            $defaultFormSelected,
                            explode(",", $form['value'])
                        )
                    )
                );

                $data = [
                    'scope' => $form['scope'],
                    'scope_id' => $form['scope_id'],
                    'path' => $form['path'],
                    'value' => $newValue
                ];

                $writeConnection->insertOnDuplicate(
                    $setup->getTable('core_config_data'),
                    $data
                );

                $writeConnection->delete(
                    $setup->getTable('core_config_data'),
                    ['path=?'=>'customer/captcha/enable']
                );
            }
        }
    }

}
