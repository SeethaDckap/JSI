<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Helper;

use  \Epicor\Comm\Model\Customer\Erpaccount;
use  Magento\Customer\Model\Address;
use  Magento\Store\Model\ScopeInterface;
use \Magento\Framework\Pricing\PriceCurrencyInterface;

class Data extends \Epicor\Common\Helper\Data
{
    const TYPE_GUEST_LOGGED_IN = 'logged_in';
    const TYPE_GUEST_NOT_LOGGED_IN = 'not_logged_in';

    /**
     * CSV Format.
     */
    const CSV_APPLIED_FORMAT = [
        'application/vnd.ms-excel',
        'text/csv'
    ];

    const XML_PATH_PRODUCT_PRICE_PRECISION = "epicor_comm_enabled_messages/global_request/price_precision";
    const XML_PATH_CUSTOMER_ADDRESS_LIMITS_ENABLED = 'customer/address/limits_enabled';

    /**
     * Configuration path for checking if CPN is enabled in emails or not
     */
    const XML_PATH_CPN_IN_EMAILS = 'epicor_comm_enabled_messages/gor_request/cpn_in_emails';

    protected $erpAccount;

    /**
     * @var \Epicor\Comm\Model\Message\Request\LicsFactory
     */
    protected $commMessageRequestLicsFactory;

    /**
     * @var \Epicor\Comm\Model\BrandingFactory
     */
    protected $commBrandingFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $customerAddressFactory;

    /**
     * @var \Epicor\Comm\Model\Message\Request\AstFactory
     */
    protected $commMessageRequestAstFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * @var \Magento\Shipping\Model\ConfigFactory
     */
    protected $shippingConfigFactory;

    /**
     * @var \Magento\Backup\Model\BackupFactory
     */
    protected $backupBackupFactory;

    /**
     * @var \Magento\Backup\Model\DbFactory
     */
    protected $backupDbFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory
     */
    protected $commResourceCustomerErpaccountAddressCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory
     */
    protected $commCustomerErpaccountAddressFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\Address\StoreFactory
     */
    protected $commCustomerErpaccountAddressStoreFactory;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected $customerconnectMessageRequestCuau;

    /**
     * @var \Epicor\Comm\Model\Config\Source\CheckoutaddressFactory
     */
    protected $commConfigSourceCheckoutaddressFactory;

    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    protected $configConfigSourceYesnoFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $directoryResourceModelRegionCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Sku\CollectionFactory
     */
    protected $commResourceCustomerSkuCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Message\Request\MsqFactory
     */
    protected $commMessageRequestMsqFactory;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * /**
     * @var \Epicor\Comm\Model\Erp\Mapping\AttributesFactory
     */
    protected $commErpMappingAttributesFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Mapping\AttributesFactory
     */
    protected $commResourceErpMappingAttributesFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $storeGroup;
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var \Magento\Theme\Model\Data\Design\Config
     */
    protected $designConfig;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemeProviderInterface
     */
    protected $themeProvider;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     * @since 100.2.0
     */
    protected $serializer;

    protected $commentVariable = [
        'additional_text',
        'note_text',
        'claim_comment',
        'web_comment'
    ];

    /**
     * @var \Epicor\Comm\Model\ArrayMessages
     */
    protected $arrayMessages;

    protected $customerSessionFactoryExist=null;

    protected $genericFactoryExist=null;

    protected $customerErpAccountinfo=null;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ShippingstatusFactory
     */
    protected $commErpMappingShippingstatusFactory;

    /**
     * @var Context
     */
    private $context;

    public function __construct(
        \Epicor\Comm\Helper\Context $context
    ) {
        $this->context = $context;
        $this->scopeConfig = $context->getScopeConfig();
        $this->checkoutCart = $context->getCheckoutCart();
        $this->urlEncoder = $context->getUrlEncoder();
        $this->urlDecoder = $context->getUrlDecoder();
        $this->commMessageRequestLicsFactory = $context->getCommMessageRequestLicsFactory();
        $this->commBrandingFactory = $context->getCommBrandingFactory();
        $this->commCustomerErpaccountFactory = $context->getCommCustomerErpaccountFactory();
        $this->customerAddressFactory = $context->getCustomerAddressFactory();
        $this->commMessageRequestAstFactory = $context->getCommMessageRequestAstFactory();
        $this->commResourceCustomerErpaccountCollectionFactory = $context->getCommResourceCustomerErpaccountCollectionFactory();
        $this->shippingConfig = $context->getShippingConfig();
        $this->shippingConfigFactory = $context->getShippingConfigFactory();
        $this->backupBackupFactory = $context->getBackupBackupFactory();
        $this->backupDbFactory = $context->getBackupDbFactory();
        $this->commResourceCustomerErpaccountAddressCollectionFactory = $context->getCommResourceCustomerErpaccountAddressCollectionFactory();
        $this->commCustomerErpaccountAddressFactory = $context->getCommCustomerErpaccountAddressFactory();
        $this->commCustomerErpaccountAddressStoreFactory = $context->getCommCustomerErpaccountAddressStoreFactory();
        $this->customerconnectMessageRequestCuau = $context->getCustomerconnectMessageRequestCuau();
        $this->commConfigSourceCheckoutaddressFactory = $context->getCommConfigSourceCheckoutaddressFactory();
        $this->configConfigSourceYesnoFactory = $context->getConfigConfigSourceYesnoFactory();
        $this->directoryResourceModelRegionCollectionFactory = $context->getDirectoryResourceModelRegionCollectionFactory();
        $this->catalogProductFactory = $context->getCatalogProductFactory();
        $this->commResourceCustomerSkuCollectionFactory = $context->getCommResourceCustomerSkuCollectionFactory();
        $this->commMessageRequestMsqFactory = $context->getCommMessageRequestMsqFactory();
        $this->listsListModelFactory = $context->getListsListModelFactory();
        $this->response = $context->getResponse();
        $this->orderFactory = $context->getOrderFactory();
        $this->storeGroup = $context->getStoreGroup();
        $this->productMetadata = $context->getProductMetadata();
        $this->designConfig = $context->getDesignConfig();
        $this->themeProvider = $context->getThemeProvider();
        $this->httpContext = $context->getHttpContext();
        $this->listsListModelFactory = $context->getListsListModelFactory();
        $this->commErpMappingAttributesFactory = $context->getCommErpMappingAttributesFactory();
        $this->commResourceErpMappingAttributesFactory = $context->getCommResourceErpMappingAttributesFactory();
        $this->serializer = $context->getSerializer();
        $this->customerRepository = $context->getCustomerRepository();
        $this->eventManager = $context->getEventManager();
        $this->arrayMessages = $context->getArrayMessages();
        $this->commErpMappingShippingstatusFactory = $context->getCommErpMappingShippingstatusFactory();
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Url\EncoderInterface
     */
    public function getUrlEncoder()
    {
        return $this->urlEncoder;
    }


    /**
     * @return \Magento\Framework\Url\DecoderInterface
     */
    public function getUrlDecoder()
    {
        return $this->urlDecoder;
    }

    public function checkForceMasqurading()
    {
        // [Start Force Masqurade]

        $customerSession = $this->customerSessionFactory();
        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */
        if ($customer->getCustomerErpAccount()->hasChildAccounts('T') && ($customerSession->getMasqueradeAccountId() === false || ($customerSession->getMasqueradeAccountId() !== false && !$this->getErpAccountInfo()->isBrandingValidOnStore()))) {
            //find child erp account valid for current store
            $customer->setValidMasquradeAccountForStore('T');
        }

        // [End Force Masqurade]
    }

    public function licfor()
    {
        echo "<pre>";
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //$filename = Mage::getBaseDir() . DS . $this->scopeConfig->getValue('Epicor_Comm/licensing/cert_file', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $filename = $this->directoryList->getRoot() . DIRECTORY_SEPARATOR . $this->scopeConfig->getValue('Epicor_Comm/licensing/cert_file',
                ScopeInterface::SCOPE_STORE);
        $newFileName = $this->directoryList->getPath('pub') . DIRECTORY_SEPARATOR . $this->scopeConfig->getValue('Epicor_Comm/licensing/cert_file',
                ScopeInterface::SCOPE_STORE);
        if(file_exists($newFileName)){
            $filename = $newFileName;
        }

        //M1 > M2 Translation End
        if (file_exists($filename)) {
            $valid_types = array();
            $file_data = file_get_contents($filename);
            $file_data = $this->decryptWithPassword($file_data,
                'Epicor_Encrypt' . $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/url',
                    ScopeInterface::SCOPE_STORE) . 'violin1234', false);
            var_dump($file_data);
            try {
                $ini_data = parse_ini_string($file_data, true);
            } catch (\Exception $e) {

            }
            if (isset($ini_data['licenseTypes'])) {
                foreach ($ini_data['licenseTypes'] as $key => $value) {
                    if ($value == 'Y') {
                        $valid_types[] = $key;
                    }
                }
            }
            var_dump($valid_types);
        } else {
            echo "File not found";
        }
    }

    public function genecclic()
    {

        $data = 'Access Denied';


        if ($this->request->isPost()) {

            $request = $this->request;
            $url = $request->getPost('Erp_Url');
            $url = empty($url) ? $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/url',
                ScopeInterface::SCOPE_STORE) : $url;
            $msg = $this->commMessageRequestLicsFactory->create();
            /* @var $msg Epicor_Comm_Model_Message_Request_Lics */
            $data = "[licenseTypes]\n";
            $data .= "Consumer=" . $request->getPost('Consumer', 'N') . "\n";
            $data .= "Customer=" . $request->getPost('Customer', 'N') . "\n";
            $data .= "Supplier=" . $request->getPost('Supplier', 'N') . "\n";
            $data .= "Ios=" . $request->getPost('Ios', 'N') . "\n";
            $data .= "Android=" . $request->getPost('Android', 'N') . "\n";
            $data .= "Consumer_Configurator=" . $request->getPost('Consumer_Configurator', 'N') . "\n";
            $data .= "Customer_Configurator=" . $request->getPost('Customer_Configurator', 'N') . "\n";
            $data .= "Dealer_Portal=" . $request->getPost('Dealer_Portal', 'N') . "\n";
            $data .= "[licenseInfo]\n";
            // $data = chunk_split(base64_encode($data));
            $data = chunk_split($this->encryptWithPassword($data, 'Epicor_Encrypt' . $url . 'violin1234', false),
                32);

            header('Content-type: text/plain');

            // It will be called ecc.lic
            header('Content-Disposition: attachment; filename="ecc.lic"');
        } else {
            $data = '
                <style>
                    ul {
                        padding:0;
                        margin-top:0;
                    }
                    li {
                        list-style:none;
                        width:220px;
                        overflow:hidden;
                    }
                    label {
                        float:right;
                        width:190px;
                    }
                    li input {
                        float:left;
                    }
                    form {
                        padding:0 20px 20px;
                    }
                </style>
                <h1>Generate ECC License Form</h1>
                <form method="POST">
                    <ul>
                        <li style="width:500px">
                            <strong>Erp Url</strong> :
                            ' . $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/url',
                    ScopeInterface::SCOPE_STORE) . '
                        </li>
                        <li>
                            <label for="Consumer">Consumer Connect</label>
                            <input type="checkbox" value="Y" id="Consumer" name="Consumer" checked />
                        </li>
                        <li>
                            <label for="Customer">Customer Connect</label>
                            <input type="checkbox" value="Y" id="Customer" name="Customer" checked />
                        </li>
                        <li>
                            <label for="Supplier">Supplier Connect</label>
                            <input type="checkbox" value="Y" id="Supplier" name="Supplier" checked />
                        </li>
                        <li>
                            <label for="Ios">Ios</label>
                            <input type="checkbox" value="Y" id="Ios" name="Ios" checked />
                        </li>
                        <li>
                            <label for="Android">Android</label>
                            <input type="checkbox" value="Y" id="Android" name="Android" checked />
                        </li>
                        <li>
                            <label for="Consumer_Configurator">Consumer Configurator</label>
                            <input type="checkbox" value="Y" id="Consumer_Configurator" name="Consumer_Configurator" checked />
                        </li>
                        <li>
                            <label for="Customer_Configurator">Customer Configurator</label>
                            <input type="checkbox" value="Y" id="Customer_Configurator" name="Customer_Configurator" checked />
                        </li>
                        <li>
                            <label for="Dealer_Portal">Dealer Portal</label>
                            <input type="checkbox" value="Y" id="Dealer_Portal" name="Dealer_Portal" checked />
                        </li>
                    </ul>
                    <input type="submit" value="Generate ECC License" />
                 </form>
            ';
        }

        return $data;
    }

    /**
     *
     * @param int $storeId
     * @return \Epicor\Comm\Model\Branding
     */
    public function getStoreBranding($storeId = null)
    {
        $branding = $this->commBrandingFactory->create();

        $view = $this->storeManager->getStore($storeId);
        $store = $view->getGroup();
        $website = $view->getWebsite();

        $branding->setCompany(
            $website->getEccCompany() ?:
                $store->getEccCompany() ?:
                    $this->scopeConfig->getValue('Epicor_Comm/licensing/company',
                        ScopeInterface::SCOPE_STORE, $storeId)
        );

        $branding->setSite($website->getEccSite() ?: $store->getEccSite());
        $branding->setWarehouse($website->getEccWarehouse() ?: $store->getEccWarehouse());
        $branding->setGroup($website->getEccGroup() ?: $store->getEccGroup());

        return $branding;
    }

    /**
     *
     * @param int $websiteId
     * @return \Epicor\Comm\Model\Branding
     */
    public function getWebsiteBranding($websiteId = null)
    {
        $branding = $this->commBrandingFactory->create();

        $website = $this->storeManager->getWebsite($websiteId);

        $branding->setCompany(
            $website->getCompany() ?:
                $this->scopeConfig->getValue('Epicor_Comm/licensing/company',
                    ScopeInterface::SCOPE_STORE, $website->getDefaultStore()->getId())
        );

        $branding->setSite($website->getEccSite());
        $branding->setWarehouse($website->getEccWarehouse());
        $branding->setGroup($website->getEccGroup());

        return $branding;
    }

    public function getDefaultStoresFromBranding($company, $site, $warehouse, $group)
    {
        $stores = array();
        $all_matching_stores = $this->getStoreFromBranding($company, $site, $warehouse, $group);
        foreach ($all_matching_stores as $store) {
            /* @var $store Mage_Core_Model_Store */
            $store_id = $store->getGroup()->getDefaultStoreId();
            if (!array_key_exists($store_id, $stores)) {
                $stores[$store_id] = $store->getGroup()->getDefaultStore();
            }
        }
        return $stores;
    }

    public function getDefaultStores()
    {
        $stores = array();
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            if ($website->getDefaultStore()) {
                $stores[$website->getDefaultStore()->getId()] = $website->getDefaultStore();
            }
        }
        return $stores;
    }

    /**
     * Get all stores that match the branding
     * null values are wildcards.
     * @param string $company
     * @param string $site
     * @param string $warehouse
     * @param string $group
     * @return array
     */
    public function getStoreFromBranding($company, $site = null, $warehouse = null, $group = null)
    {
        $stores = array();
        $mage_stores = $this->storeManager->getStores();
        foreach ($mage_stores as $store) {

            $store_company = $store->getWebsite()->getEccCompany() ?: $store->getGroup()->getEccCompany();
            $store_site = $store->getWebsite()->getEccSite() ?: $store->getGroup()->getEccSite();
            $store_warehouse = $store->getWebsite()->getEccWarehouse() ?: $store->getGroup()->getEccWarehouse();
            $store_group = $store->getWebsite()->getEccGroup() ?: $store->getGroup()->getEccGroup();
            if (
                ($store_company == null || $company == null || $store_company == $company) &&
                ($store_site == null || $site == null || strcasecmp($store_site, $site) == 0) &&
                ($store_warehouse == null || $warehouse == null || $store_warehouse == $warehouse) &&
                ($store_group == null || $group == null || $store_group == $group)
            ) {
                $stores[$store->getId()] = $store;
            }
        }

        return $stores;
    }

    /**
     * Returns the Minimum Order value
     * @param \Epicor\Comm\Model\Erp\Customer\Group $erpGroup
     * @return Decimal
     */
    public function getMinimumOrderAmount($erpAccountId = 0)
    {
        $value = 0;

        if ($erpAccountId == 0) {
            $erpAccountId = $this->scopeConfig->getValue('customer/create_account/default_erpaccount',
                ScopeInterface::SCOPE_STORE);
        }

        $erpAccount = $this->commCustomerErpaccountFactory->create()->load($erpAccountId);
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

        $magentoAmount = $this->scopeConfig->getValue('sales/minimum_order/amount',
            ScopeInterface::SCOPE_STORE);

        switch ($this->scopeConfig->getValue('epicor_comm_field_mapping/cus_mapping/cus_min_order',
            ScopeInterface::SCOPE_STORE)) {
            case \Epicor\Comm\Model\Customer\Erpaccount::MIN_ORDER_SOURCE_ERP:
                $value = $erpAccount->getMinOrderAmount();
                break;
            case \Epicor\Comm\Model\Customer\Erpaccount::MIN_ORDER_SOURCE_HIGHER:
                if($erpAccount->getMinOrderAmountFlag() || $this->isGlobalFlagCusHighLowMagentoDisabled($erpAccount)){
                    $value = $erpAccount->getMinOrderAmount();
                }else{
                    $value = max($erpAccount->getMinOrderAmount(), $magentoAmount);
                }
                break;
            case \Epicor\Comm\Model\Customer\Erpaccount::MIN_ORDER_SOURCE_LOWER:
                if($erpAccount->getMinOrderAmountFlag() || $this->isGlobalFlagCusHighLowMagentoDisabled($erpAccount)){
                    $value = $erpAccount->getMinOrderAmount();
                }else{

                    $value = min($erpAccount->getMinOrderAmount(), $magentoAmount);
                }

                break;
            case \Epicor\Comm\Model\Customer\Erpaccount::MIN_ORDER_SOURCE_MAGENTO:
                if($erpAccount->getMinOrderAmountFlag()){
                    $value = $erpAccount->getMinOrderAmount();
                }
                break;
            default:
                $value = $magentoAmount;
                break;
        }
        return $value;
    }

    private function getCusMinOrderSetting(): string
    {
        return $this->scopeConfig->getValue('epicor_comm_field_mapping/cus_mapping/cus_min_order');
    }

    private function isCusSettingHighLow(): bool
    {
        return in_array($this->getCusMinOrderSetting(), ['higher','lower']) ? true : false;
    }

    private function isGlobalFlagCusHighLowMagentoDisabled($erpAccount)
    {
        if ($erpAccount) {
            return !$erpAccount->getMinOrderAmountFlag()
                && !$this->isSiteMinOrderEnabled()
                && $this->isCusSettingHighLow();
        }
    }

    private function isSiteMinOrderEnabled()
    {
        return (bool) $this->scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     *
     * @param type $accountNumber
     * @return boolean
     */
    public function getErpAddress($customerAddressId, $accountNumber)
    {
        $erp_address = $this->customerAddressFactory->create()->load($customerAddressId);

        $erpAddressCode = $erp_address->getEccErpAddressCode();
        if (
            !$erpAddressCode
            && $erpAddressCode !== 0
            && $erpAddressCode !== "0"
        ) {
            $erp_address->setEccErpAddressCode($this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_address_code',
                ScopeInterface::SCOPE_STORE));
        };

        return $erp_address;
    }

    /**
     * Returns the maximum order value for a given salesorder based on payment type.
     * This is indtended to be used when editing an order to avoid payment problems
     */
    public function getMaximumOrderAmount($orderId)
    {
        //M1 > M2 Translation Begin (Rule p2-1)
        //$order = Mage::getModel('sales_order')->load($orderId);
        $order = $this->orderFactory->create()->load($orderId);
        //M1 > M2 Translation End
        /* @var $order Mage_Sales_Model_Order */
        if ($order->isObjectNew()) {
            return 0;
        } else {
            $currentAmount = $order->getGrandTotal();
            $allowedAdditioanlAmount = $this->getMaxAdditionalCharge($order);
            return $currentAmount + $allowedAdditioanlAmount;
        }
    }

    /**
     * returns the order amount of the initial order as authorised prior to any modification
     * @param \Magento\Sales\Model\Order $order
     * @return type
     */
    private function getOrderInitialTotal($order)
    {
        $originalAmount = $order->getEccInitialGrandTotal();
        if ($originalAmount == null) {
            $originalAmount = $order->getGrandTotal();
            $order->setEccInitialGrandTotal($originalAmount);
            $order->save();
        }
        return $originalAmount;
    }

    /**
     * Returns the additional value that is allowed to be charged on the given order based on the payment method.
     * @param \Magento\Sales\Model\Order $order
     */
    public function getMaxAdditionalCharge($order)
    {
        $configValue = $this->scopeConfig->getValue('Epicor_Comm/payments/max_order_value',
            ScopeInterface::SCOPE_STORE);
        $configArray = unserialize($configValue);

        $paymentMethod = $order->getPayment()->getMethod();
        $type = 'fixed';
        foreach ($configArray as $config) {
            if ($config['paymentmethod'] == $paymentMethod) {
                $type = $config['chargetype'];
                $rawAmount = $config['amount'];
                break;
            } else {
                if ($config['paymentmethod'] == 'ALL') {
                    $type = $config['chargetype'];
                    $rawAmount = $config['amount'];
                }
            }
        }

        if ($type == 'fixed') {
            $amount = $rawAmount ?: 0;
        } else {
            $percentAmount = $rawAmount / 100;
            $amount = $percentAmount * $this->getOrderInitialTotal($order);
        }

        $diff = $order->getGrandTotal() - $this->getOrderInitialTotal($order);

        return $amount - $diff;
    }

    public function getSupplierAccountInfo($erpAccountId = null, $storeId = null)
    {
        return $this->getErpAccountInfo($erpAccountId, 'supplier', $storeId);
    }

    /**
     * Get Erp Account Information
     *
     * @param int $erpAccountId
     * @param string $type
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getErpAccountInfo(
        $erpAccountId = null,
        $type = 'customer',
        $storeId = null,
        $allowMasquerade = true
    ) {

        \Magento\Framework\Profiler::start('getErpAccountInfo');
        if (empty($erpAccountId)) {
            // Check if customer is logged in

            // we cant use $this->customerSessionFactory();
            // becasue at the time of logged it  took default  in MSQ
            //though registry we handeled it above issue

            $customerSession = $this->customerSessionFactory();
            /* @var $customerSession \Magento\Customer\Model\Session */
            $coreSession = $this->genericFactory();

            if ($coreSession->getTempGroupId() != null) {
                $erpAccountId = $coreSession->getTempGroupId();
            } else if ($allowMasquerade && $customerSession->getMasqueradeAccountId()) {
                $erpAccountId = $customerSession->getMasqueradeAccountId();
            } else if ($type == 'supplier' && $this->_moduleManager->isEnabled('Epicor_Supplierconnect')) {
                $erpAccountId = $customerSession->getCustomer()->getEccSupplierErpaccountId();
            } else {
                $erpAccountId = $customerSession->getCustomer()->getEccErpaccountId();
            }

            if (empty($erpAccountId)) {
                $erpAccountId = $this->scopeConfig->getValue(
                    'customer/create_account/default_erpaccount',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
            }
        }

        $erpAccount = $this->registry->registry('ecc_erp_account_' . $erpAccountId);
        if (empty($erpAccount)) {
            $erpAccount = $this->commCustomerErpaccountFactory->create()->load($erpAccountId);
            $this->registry->register('ecc_erp_account_' . $erpAccountId, $erpAccount, true);
            if ($erpAccount->isObjectNew()) {
                if ($erpAccountId == null) {
                    if ($this->checkLastMessageTime()) {
                        $message = 'Default ERP account is not set or does not exist in ECC';
                        $title = 'No Default ERP account';
                        $this->sendMagentoMessage($message, $title,
                            \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL);
                    }
                } else {
                    if ($erpAccountId) {
                        $message = 'Erp Account Id ' . $erpAccountId . ' Does not exist';
                    } else {
                        $message = 'Erp Account for customer ' . $customerSession->getCustomer()->getEmail() . ' no longer exists';
                    }
                    $title = 'Erp Account';
                    $message .= '<br /> Likely Causes: A deleted ERP Account is linked to a Customer / Order or was set as the default ERP Account for a store';
                    $message .= '<div style="display:none"><br />Debug Information:';
                    $message .= $this->backtrace(false, true) . '</div>';
                    $this->sendMagentoMessage($message, $title, \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL);
                }

                $erpAccount = false;
            }
        }

        \Magento\Framework\Profiler::stop('getErpAccountInfo');
        return $erpAccount;
    }

    public function getSupplierAccountNumber($erpAccountId = null, $storeId = null)
    {
        return $this->getAccountNumber($this->getSupplierAccountInfo($erpAccountId, $storeId));
    }

    public function getErpAccountNumber($erpAccountId = null, $storeId = null)
    {
        return $this->getAccountNumber($this->getErpAccountInfo($erpAccountId, 'customer', $storeId));
    }

    public function startMasquerade($erpAccountId)
    {
        $customerSession = $this->customerSessionFactory();
        /* @var $customerSession \Magento\Customer\Model\Session */
        $customerSession->setMasqueradeAccountId($erpAccountId);

        $this->_eventManager->dispatch('epicor_comm_masquerade_start',
            array('request' => $this->request, 'customer' => $customerSession->getCustomer()));

        $ast = $this->commMessageRequestAstFactory->create();
        /* @var $ast \Epicor\Comm\Model\Message\Request\Ast */
        $ast->sendMessage();
    }

    public function stopMasquerade()
    {
        $customerSession = $this->customerSessionFactory();
        /* @var $customerSession Mage_Customer_Model_Session */

        $this->_eventManager->dispatch('epicor_comm_masquerade_end',
            array('request' => $this->request, 'customer' => $customerSession->getCustomer()));

        $customerSession->setMasqueradeAccountId(null);

        $ast = $this->commMessageRequestAstFactory->create();
        /* @var $ast Epicor_Comm_Model_Message_Request_Ast */
        $ast->sendMessage();
    }

    public function isMasquerading()
    {
        $customerSession = $this->customerSessionFactory();
        /* @var $customerSession \Magento\Customer\Model\Session */
        $masquerade = $customerSession->getMasqueradeAccountId();

        return !empty($masquerade);
    }

    public function customerSessionFactory()
    {
        if (!$this->customerSessionFactoryExist) {
            $this->customerSessionFactoryExist = $this->customerSessionFactory->create();
        }

        if ($this->registry->registry('after_login_msq') ){
            $this->customerSessionFactoryExist = $this->customerSessionFactory->create();
            $this->registry->unregister('after_login_msq');

        }
        return $this->customerSessionFactoryExist;
    }

    public function genericFactory()
    {
        if (!$this->genericFactoryExist) {
            $this->genericFactoryExist = $this->genericFactory->create();
        }
        return $this->genericFactoryExist;
    }

    public function customerErpAccountInfo() {
        if (!$this->customerErpAccountinfo) {
            $this->customerErpAccountinfo = $this->getErpAccountInfo();
        }
        return $this->customerErpAccountinfo;
    }

    /**
     * @param $functionality
     * @param null $customerId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isFunctionalityDisabledForCustomer($functionality, $customerId = null)
    {
        $isDisabled = false;

        $restriction = $this->scopeConfig->getValue('customer/onstop/restriction',
            ScopeInterface::SCOPE_STORE);

        $customerSession = $this->customerSessionFactory();
        /* @var $customerSession Mage_Customer_Model_Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Common_Model_Customer */

        if (!is_null($customerId)) {
            $customer = $this->customerCustomerFactory->create()->load($customerId);
        }

        if ($functionality == 'multishipping') {
            $cart = $this->checkoutCart;
            /* @var $cart Epicor_Comm_Model_Cart */
            $quote = $cart->getQuote();
            /* @var $customer Epicor_Comm_Model_Quote */
            if ($quote->getEccQuoteId()) {
                $isDisabled = true;
            }
        }

        $_accessauthorization = $this->catalogProductFactory->create()->getAccessAuthorization();
        if ($functionality == 'cart' && !$_accessauthorization->isAllowed(
                'Epicor_Checkout::checkout_checkout_can_checkout'
            )) {
            $isDisabled = true;
        }
        if ($functionality == 'checkout') {
            $online = $this->scopeConfig->isSetFlag('Epicor_Comm/xmlMessaging/failed_msg_online',
                ScopeInterface::SCOPE_STORE);
            $commsDisabled = $this->scopeConfig->isSetFlag('Epicor_Comm/xmlMessaging/disable_comms',
                ScopeInterface::SCOPE_STORE);
            $checkoutDisabled = $this->scopeConfig->isSetFlag('Epicor_Comm/xmlMessaging/site_offline_checkout_disabled',
                ScopeInterface::SCOPE_STORE);
            if ((!$online || $commsDisabled) && $checkoutDisabled) {
                $isDisabled = true;
            }
        }

        if (!$isDisabled && $restriction != 'none') {

            $erpAccount = $this->customerErpAccountInfo();
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
            $onStop = false;

            if ($erpAccount && !$erpAccount->isObjectNew()) {
                $onStop = $erpAccount->getOnstop($this->storeManager->getStore()->getBaseCurrencyCode());
            }

            if ($functionality == 'cart') {
                if (in_array($restriction, array('cart_checkout', 'login')) && $onStop) {
                    $isDisabled = true;
                }
            }

            if ($functionality == 'checkout') {
                if (in_array($restriction, array('cart_checkout', 'checkout', 'login')) && $onStop) {
                    $isDisabled = true;
                }
            }
        }

        if (!$isDisabled && $this->scopeConfig->isSetFlag('customer/disabled_functionality/active',
                ScopeInterface::SCOPE_STORE)) {

            $disabledList = array();
            if ($customerSession->isLoggedIn() || !is_null($customerId)) {
                if ($customer->isGuest()) {
                    $disabledList = unserialize($this->scopeConfig->getValue('customer/disabled_functionality/guests_logged_in',
                        ScopeInterface::SCOPE_STORE));
                }
            } else {
                $disabledList = unserialize($this->scopeConfig->getValue('customer/disabled_functionality/guests_logged_out',
                    ScopeInterface::SCOPE_STORE));
            }
            if ($disabledList === false) {
                $disabledList = array();
            }
            if (in_array($functionality, $disabledList)) {
                $isDisabled = true;
            }
        }

        if (!$isDisabled && $customer->isSupplier()) {
            if (in_array($functionality, array('multishipping', 'checkout', 'cart'))) {
                $isDisabled = true;
            }
        }

        $transportObject = $this->dataObjectFactory->create();
        $transportObject->setDisabled($isDisabled);
        $this->_eventManager->dispatch('epicor_comm_is_functionality_disabled_after',
            array('functionality' => $functionality, 'transport' => $transportObject));
        $isDisabled = $transportObject->getDisabled();

        return $isDisabled;
    }

    public function canCustomerAccessUrl($url)
    {
        $url = strtok($url, '?');
        $restriction = $this->scopeConfig->getValue('customer/onstop/restriction',
            ScopeInterface::SCOPE_STORE);

        $helper = $this->commonAccessHelper;
        /* @var $helper Epicor_Common_Helper_Access */

        $urlInfo = $helper->getModuleInfoFromUrl($url);
        $allow = true;

        $customerSession = $this->customerSessionFactory();
        /* @var $customerSession Mage_Customer_Model_Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Common_Model_Customer */

        if ($restriction != 'none') {

            $erpAccount = $this->getErpAccountInfo();

            if ($erpAccount && !$erpAccount->isObjectNew() && $erpAccount->getOnstop()) {
                if (strpos($url, 'checkout') !== false) {
                    $controllerName = $urlInfo['controller'];
                    if (($restriction == 'checkout' && (in_array($urlInfo['route'], array('onepage', 'multishipping', ''))))
                        || in_array($restriction, array('cart_checkout', 'login'))) {
                        $allow = false;
                    }
                    if (($restriction == 'cart_checkout' && (in_array($urlInfo['route'], array('checkout', ''))))
                        || in_array($restriction, array('cart_checkout', 'login'))) {
                        $allow = false;
                    }
                } else {
                    if (strpos($url, 'wishlist') !== false) {
                        $action = $urlInfo['action'];
                        if ($action == 'cart' && in_array($restriction, array('cart_checkout', 'login'))) {
                            $allow = false;
                        }
                    } else {
                        if (strpos($url, 'reorder') !== false) {
                            if (in_array($restriction, array('cart_checkout', 'login'))) {
                                $allow = false;
                            }
                        }
                    }
                }
            }
        }

        if ($allow) {
            if (strpos($url, 'checkout') !== false) {
                $controllerName = strtolower($urlInfo['controller']);
                $eccHidePrice = $this->getEccHidePrice();
                if (($eccHidePrice && $eccHidePrice != 3) ||$this->isFunctionalityDisabledForCustomer('cart')
                    || ($urlInfo['route'] == 'multishipping' && $this->isFunctionalityDisabledForCustomer('multishipping'))
                    || ($this->isFunctionalityDisabledForCustomer('checkout') && in_array($urlInfo['route'], array('checkout', 'multishipping', '')) &&  in_array($controllerName, array('index'))))
                {
                    $allow = false;
                }
            } else {
                if ((strpos($url, 'reorder') !== false || (strpos($url,'wishlist') !== false && $urlInfo['action'] == 'cart'))
                    && $this->isFunctionalityDisabledForCustomer('cart')) {
                    $allow = false;
                }
            }
        }

        return $allow;
    }

    /**
     * Loads an ERP Account by Account Number
     * @param string  $accountCode ERP Code.
     * @param string  $type        Account type.
     * @param boolean $acctCheck   Account check required.
     *
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getErpAccountByAccountNumber($accountCode, $type = 'Customer', $acctCheck = false)
    {
        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $collection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Collection */
        $collection->addFieldToFilter('erp_code', $accountCode);

        if (!$acctCheck) {
            if ($type == 'Customer') {
                $types = Erpaccount::$_All_ErpAccountsTypes_List;
                // $types = array('B2B', 'B2C');
                $collection->addFieldToFilter('account_type', array('IN' => $types));
            } else {
                $collection->addFieldToFilter('account_type', $type);
            }
        }

        $erpAccountData = $collection->getFirstItem();
        $erpAccount = $this->commCustomerErpaccountFactory->create();

        if ($erpAccountData->getId()) {
            $erpAccount = $erpAccount->load($erpAccountData->getId());
        }

        return $erpAccount;
    }

    private static function getAccountNumber($data)
    {
        $accountNumber = false;

        if ($data) {
            $accountNumber = $data->getErpCode();
        }
        return $accountNumber;
    }

    /**
     * Gets the prioduct options from a sales quote / sales order item
     * @param \Magento\Quote\Model\Quote\Item/Mage_Sales_Order_Item $item
     */
    public function getItemProductOptions($item)
    {

        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            /* @var $$item Mage_Sales_Order_Item */
            $productOptions = $item->getProductOptions();
        } else {
            /* @var $$item Mage_Sales_Model_Quote_Item */
            $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        }
        return $productOptions;
    }

    public function getShippingmethodList($activeOnly = false)
    {

        $allOptions = array();
        $carriers = array();

        if ($activeOnly) {
            $source = $this->shippingConfig->getActiveCarriers();
        } else {
            $source = $this->shippingConfigFactory->create()->getAllCarriers();
        }

        foreach ($source as $carrier) {
            /* @var $carrier Amasty_Table_Model_Carrier_Table */
            $methods = array();
            foreach ($carrier->getAllowedMethods() as $method => $label) {
                $methods[] = array(
                    'value' => $carrier->getCarrierCode() . '_' . $method,
                    'label' => $label
                );
                $allOptions[$carrier->getCarrierCode() . '_' . $method] = $label;
            }
            $carriers[] = array(
                'value' => $methods,
                'label' => $this->scopeConfig->getValue('carriers/' . $carrier->getCarrierCode(ScopeInterface::SCOPE_STORE) . '/title')
            );
        }

        if (!$this->registry->registry('shipping_carriers')) {
            $this->registry->register('shipping_carriers', $allOptions);
        }
        return $carriers;
    }

    public function tableBackup($table)
    {

        $backup = $this->backupBackupFactory->create()
            ->setTime(time())
            ->setType('db')
            //M1 > M2 Translation Begin (Rule p2-5.5)
            //->setPath(Mage::getBaseDir("var") . DS . "backups");
            ->setPath($this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . "backups");
        //M1 > M2 Translation End
        $model = $this->backupDbFactory->create();
        $backup->open(true);

        $model->getResource()->beginTransaction();

        $backup->write($model->getResource()->getHeader());

        $backup->write($model->getResource()->getTableHeader($table)
            . $model->getResource()->getTableDropSql($table) . "\n");
        $backup->write($model->getResource()->getTableCreateSql($table, false) . "\n");

        $tableStatus = $model->getResource()->getTableStatus($table);

        if ($tableStatus->getRows()) {
            $backup->write($model->getResource()->getTableDataBeforeSql($table));
            if ($tableStatus->getDataLength() > $model::BUFFER_LENGTH) {
                if ($tableStatus->getAvgRowLength() < $model::BUFFER_LENGTH) {
                    $limit = floor($model::BUFFER_LENGTH / $tableStatus->getAvgRowLength());
                    $multiRowsLength = ceil($tableStatus->getRows() / $limit);
                } else {
                    $limit = 1;
                    $multiRowsLength = $tableStatus->getRows();
                }
            } else {
                $limit = $tableStatus->getRows();
                $multiRowsLength = 1;
            }

            for ($i = 0; $i < $multiRowsLength; $i++) {
                $backup->write($model->getResource()->getTableDataSql($table, $limit, $i * $limit));
            }

            $backup->write($model->getResource()->getTableDataAfterSql($table));
        }
        $backup->write($model->getResource()->getTableForeignKeysSql());
        $backup->write($model->getResource()->getFooter());

        $model->getResource()->commitTransaction();

        $backup->close();
        $this->_logger->debug('backup of table ' . $table . ' complete');
    }

    public function checkMsgAvailable($msgType)
    {
        $msgType = strtoupper($msgType);
        if ($msgType[0] == 'S') {
            $module = 'supplierconnect';
        } else {
            if (!in_array($msgType, array('CRRS'))) {
                $module = 'customerconnect';
            } else {
                $module = 'epicor_comm';
            }
        }
        $module = strtolower($module);

        if ($this->scopeConfig->getValue("{$module}_enabled_messages/{$msgType}_request/active",
            ScopeInterface::SCOPE_STORE)) {
            return true;
        } else {
            return false;
        }
    }

    public function addNewErpAddress($saveNewAddress, $isInvoice, $isDelivery)
    {

        //M1 > M2 Translation Begin (Rule p2-1)
        //$defaultAddresscode = Mage::getModel('epicor_comm_enabled_messages/global_request/default_address_code');
        $defaultAddresscode = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_address_code');
        //M1 > M2 Translation End
        $customer = $this->customerSessionFactory()->getCustomer();
        $erpAccount = $this->getErpAccountInfo($customer->getErpAccountId());

        $customerEmail = $this->customerSessionFactory()->getCustomer()->getEmail();
        $erpAccountCode = $erpAccount->getErpCode();
        $doesAddressExist = '';
        if ($isInvoice) {
            $address = $this->customerSessionFactory()->getSaveBillingAddress();
            $this->customerSessionFactory()->setSaveBillingAddress(false);
        } else {
            $address = $this->customerSessionFactory()->getSaveShippingAddress();
            $this->customerSessionFactory()->setSaveShippingAddress(false);
        }

        $defaultAddresscode = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_address_code',
            ScopeInterface::SCOPE_STORE);

        // get unique customer code
        $customerAddressesWithDefaultCode = $this->commResourceCustomerErpaccountAddressCollectionFactory->create()->addFieldToFilter('erp_customer_group_code',
            array('eq' => $erpAccountCode))
            ->addFieldToFilter('erp_code', array('like' => $defaultAddresscode))
            ->getLastItem();
        if (!$customerAddressesWithDefaultCode->isObjectNew()) {
            $uniqueCustomerCode = $customerAddressesWithDefaultCode->getErpCode() + 1;
        } else {
            $uniqueCustomerCode = $defaultAddresscode + 1;
        }
        $uniqueCustomerCode = $erpAccount->getShortCode() . '_' . $uniqueCustomerCode;


        $customerAddresses = $this->commResourceCustomerErpaccountAddressCollectionFactory->create()->addFieldToFilter('erp_customer_group_code',
            array('eq' => $erpAccountCode))->getData();
        foreach ($customerAddresses as $existingAddress) {       // check if new account address already exists
            if (trim($address['postcode']) == trim($existingAddress['postcode']) && (trim($address['firstname'] . ' ' . $address['lastname']) == trim($existingAddress['name'])) && (trim($address['street'][0]) == trim($existingAddress['address1'])) && (trim($address['street'][1]) == trim($existingAddress['address2'])) && (trim($address['city']) == trim($existingAddress['city'])) && (trim($address['region']) == trim($existingAddress['county'])) && (trim($address['country_id']) == trim($existingAddress['country'])) && (trim($address['telephone']) == trim($existingAddress['phone'])) && (trim($address['fax']) == trim($existingAddress['fax']))
            ) {

                $existingAddressObject = $this->commCustomerErpaccountAddressFactory->create()->load($existingAddress['entity_id']);
                if ($isInvoice) {                     //address exists, so update the isinvioce or isdelivery
                    $existingAddressObject->setIsInvoice(true);
                } else {
                    $existingAddressObject->setIsDelivery(true);
                }
                try {
                    $existingAddressObject->save();
                    $doesAddressExist = true;
                    break;                              // no need to continue so exit
                } catch (Exception $ex) {
                    $this->_logger->debug($ex);
                    $this->genericFactory->create()->addNotice("Unable to update existing address. Please try later");
                    return;
                }
            }
        }
        if (!$doesAddressExist) {
            if ($saveNewAddress == 'magentoErp') {
                $magentoErpAddress = $this->commCustomerErpaccountAddressFactory->create();
                $magentoErpAddress->setErpCode($uniqueCustomerCode);
                $magentoErpAddress->setErpCustomerGroupCode($erpAccountCode);
                $magentoErpAddress->setName(trim($address['firstname'] . ' ' . $address['lastname']));
                //M1 > M2 Translation Begin (Rule 9)
                //$magentoErpAddress->setAddress1($address['street'][0]); // ?
                //$magentoErpAddress->setAddress2($address['street'][1]); // ?
                $magentoErpAddress->setData('address1', $address['street'][0]);
                $magentoErpAddress->setData('address2', $address['street'][1]);
                //M1 > M2 Translation End
                $magentoErpAddress->setCity($address['city']);
                $magentoErpAddress->setCountry($address['country_id']);
                $magentoErpAddress->setPostcode($address['postcode']);
                $magentoErpAddress->setPhone($address['telephone']);
                $magentoErpAddress->setMobileNumber(array_key_exists('mobile_number',
                    $address) ? $address['mobile_number'] : null);
                $magentoErpAddress->setFax($address['fax']);
                $magentoErpAddress->setEmail($customerEmail);
                $magentoErpAddress->setCountyCode($address['region_id']); // ?
                $magentoErpAddress->setBrands($customer->getBrands());
                $magentoErpAddress->setIsRegistered(true);
                $magentoErpAddress->setIsInvoice($isInvoice);
                $magentoErpAddress->setIsDelivery($isDelivery);
                try {
                    $magentoErpAddress->save();
                    $newAddressId = $magentoErpAddress->getId();
                    $erpGroupAddressStore = $this->commCustomerErpaccountAddressStoreFactory->create();
                    $erpGroupAddressStore->setErpCustomerGroupAddress($newAddressId);
                    $erpGroupAddressStore->setStore($this->storeManager->getStore()->getId());
                    $erpGroupAddressStore->save();
                } catch (Exception $ex) {
                    $this->_logger->debug($ex);
                    $this->genericFactory->create()->addNotice("Unable to save address. Please try later");
                }
            } else {
                $message = $this->customerconnectMessageRequestCuau;
                if ($message) {             // if cuau messages are allowed, update
                    $address['name'] = $address['firstname'] . ' ' . $address['lastname'];
                    $address['county_id'] = $address['region_id'];
                    $address['country'] = $address['country_id'];
                    $address['address1'] = $address['street'][0];
                    $address['address2'] = $address['street'][1];
                    $address['email'] = $customerEmail;
                    $address['address_code'] = $uniqueCustomerCode;
                    // add address fields so that the cuau call works
                    if ($isDelivery) {
                        $message->addDeliveryAddress('A', $address);
                        $this->sendMessage($message);
                    }
                    //else {
                    // below commented out until such time as new billing address can be saved by cuau
                    // Nb actual location has changed, so will need to be rewritten (at erp level now)
                    //  $customer = Mage::getModel('customer/session')->getCustomer();
//                        $erpAccountInfo = Mage::helper('epicor_comm')->getErpAccountInfo($customer->getErpAccountId());
//                        $erpCode = $erpAccountInfo->getCompany()."_".$erpAccountInfo->getShortCode();
                    //                        $saveErpBilling = Mage::getStoreConfig("Epicor_Comm/save_new_addresses/erp_save_billing/erp");
                    //                        $saveErpBilling = Mage::getStoreConfig("Epicor_Comm/save_new_addresses/erp_save_billing_{$erpCode}");
                    //                        $message->addInvoiceAddress('A', $address);
                    //}
                }
            }
        }
    }

    public function sendMessage($message)
    {
        $helper = $this;
        $helperMessaging = $this->commMessagingHelper;
        /* @var $helper Epicor_Customerconnect_Helper_Data */
        // $accountHelper = $this->commErpaccountHelper;
        /* @var $accountHelper Epicor_Comm_Helper_Erpaccount */

        // $erp_account_number = $accountHelper->getErpAccountNumber();
        $erp_account_number = $this->getErpAccountNumber();
        $messageTypeCheck = $message->getHelper("epicor_comm/messaging")->getMessageType('CUAU');

        if ($message->isActive() && $messageTypeCheck) {
            //M1 > M2 Translation Begin (Rule p2-6.4)
            /*$message->setAccountNumber($erp_account_number)
                ->setLanguageCode($message->getHelper("epicor_comm/messaging")->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()));
            */
            $message->setAccountNumber($erp_account_number)
                ->setLanguageCode($message->getHelper("epicor_comm/messaging")->getLanguageMapping($this->_localeResolver->getLocale()->getLocaleCode()));
            //M1 > M2 Translation End
            if (!$message->sendMessage()) {
                $this->genericFactory->create()->addNotice("Error saving new address : " . $message->getStatusDescription());
            }
        }
    }

    public function formatXml($xml)
    {
        if (!empty($xml)) {
            try {
                $dom = new \DOMDocument;
                $dom->preserveWhiteSpace = false;
                $xml = trim(preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $xml));
                $dom->loadXML($xml);
                $dom->formatOutput = true;
                $xml = $dom->saveXml();
            } catch (\Exception $e) {

            }
        }
        return $xml;
    }

    public function getSaveBillingAddressErpValues()
    {
        /// temporary solution
        $resultsArray = Array();
        $resultsArray['erp_current_dropdown_value'] = false;
        $resultsArray['save_billing_address_current_value'] = false;
        return $resultsArray;
        // ----------------------- remove above when option to be reinstated

        return $resultsArray;
        $customer = $this->customerSessionFactory()->getCustomer();
        $erpAccountInfo = $this->getErpAccountInfo($customer->getErpAccountId());
        $erpCode = $erpAccountInfo->getCompany() . "_" . $erpAccountInfo->getShortCode();
        $erpDropdownValues = $this->commConfigSourceCheckoutaddressFactory->create()->toOptionArray();
        $currentErpDropdownValue = $this->scopeConfig->getValue("Epicor_Comm/save_new_addresses/{$erpCode}",
            ScopeInterface::SCOPE_STORE);
        $globalDropdownValue = $this->scopeConfig->getValue('Epicor_Comm/save_new_addresses/erp',
            ScopeInterface::SCOPE_STORE);

        $saveBillingAddressDropdownValues = $this->configConfigSourceYesnoFactory->create()->toOptionArray();
        $globalSaveBillingAddressCurrentValue = $this->scopeConfig->getValue('Epicor_Comm/save_new_addresses/erp_save_billing',
            ScopeInterface::SCOPE_STORE);
        $saveBillingAddressCurrentErpValue = $this->scopeConfig->getValue("Epicor_Comm/save_new_addresses/erp_save_billing_{$erpCode}",
            ScopeInterface::SCOPE_STORE);

        if (!isset($currentErpDropdownValue)) {
            $currentErpDropdownValue = $globalDropdownValue;
        }

        if (!isset($saveBillingAddressCurrentErpValue)) {
            $saveBillingAddressCurrentErpValue = $globalSaveBillingAddressCurrentValue;
        }

        $resultsArray = array(
            'erp_dropdown_values' => $erpDropdownValues,
            'erp_current_dropdown_value' => $currentErpDropdownValue,
            'save_billing_address_values' => $saveBillingAddressDropdownValues,
            'save_billing_address_current_value' => $saveBillingAddressCurrentErpValue
        );
        return $resultsArray;
    }

    public function getRegionNameOrCode($countryCode = null, $regionCode = null)
    {
        if (!$countryCode || !$regionCode) {      // if no country code supplied do not continue (effectively return region code if country missing or nothing if region code missing
            return $regionCode;
        }
        $currentStore = $this->storeManager->getStore()->getId();
        $sendRegionName = $this->scopeConfig->getValue('Epicor_Comm/region/name',
            ScopeInterface::SCOPE_STORE, $currentStore);

        if (is_numeric(trim($regionCode))) {
            $regionCode = $this->directoryRegionFactory->create()->load($regionCode)->getCode();        // if region code is numeric, it is the id and needs to be changed to 2 digit code
        } else {
            $regionIsSupplied = $this->directoryResourceModelRegionCollectionFactory // if actual region has been supplied, convert to code. hacky fix, maybe look at rewriting later
            ->create()
                ->addFieldToFilter('country_id', array('eq' => strtoupper($countryCode)))
                ->addFieldToFilter('default_name', array('eq' => strtoupper($regionCode)))
                ->getFirstITem();

            if (!$regionIsSupplied->isObjectNew()) {
                $regionCode = $regionIsSupplied->getCode();
            }
        }
        if ($sendRegionName) {
            $regionName = $this->directoryResourceModelRegionCollectionFactory
                ->create()
                ->addFieldToFilter('country_id', array('eq' => strtoupper($countryCode)))
                ->addFieldToFilter('code', array('eq' => strtoupper($regionCode)))
                ->getFirstItem();

            if (!$regionName->isObjectNew()) {
                return $regionName->getDefaultName();
            } else {
                return $regionCode;
            }
        } else {
            return $regionCode;
        }
    }

    public function checkLastMessageTime()
    {
        $store = $this->storeManager->getStore()->getId();
        $lastMessageWritten = $this->scopeConfig->getValue('Epicor_Comm/erp_notification/message_time',
            ScopeInterface::SCOPE_STORE, $store);
        $currentTime = strtotime("now");
        $timeDelay = 3600 * $this->scopeConfig->getValue('Epicor_Comm/no_default_erp/message',
                ScopeInterface::SCOPE_STORE); // 3600 secs = 1 hour
        $time_before_next_message = $lastMessageWritten + $timeDelay;
        if ($time_before_next_message <= $currentTime) {
            //M1 > M2 Translation Begin (Rule P2-2)
            //Mage::getConfig()->init()->saveConfig('Epicor_Comm/erp_notification/message_time', $currentTime, 'stores', $store);
            $this->resourceConfig->saveConfig('Epicor_Comm/erp_notification/message_time', $currentTime, 'stores',
                $store);
            //M1 > M2 Translation End

            $this->storeManager->getStore($store)->resetConfig();
            return true;
        } else {
            return false;
        }
    }

    public function isThemeChildOfRwd()
    {
        //M1 > M2 Translation Begin (Rule P2-5.10)
        //$version = Mage::getVersionInfo();
        $version = $this->productMetadata->getVersion();
        //M1 > M2 Translation End

        //M1 > M2 Translation Begin (Rule P2-5.12)
        //if (((Mage::getEdition() == Mage::EDITION_COMMUNITY) && $version['major'] == 1 && $version['minor'] >= 9) || ((Mage::getEdition() == Mage::EDITION_ENTERPRISE) && $version['major'] == 1 && $version['minor'] >= 14)) {
        if ((($this->productMetadata->getEdition() == Mage::EDITION_COMMUNITY) && $version['major'] == 1 && $version['minor'] >= 9) || (($this->productMetadata->getEdition() == Mage::EDITION_ENTERPRISE) && $version['major'] == 1 && $version['minor'] >= 14)) {
            //M1 > M2 Translation End

            $area = 'frontend';
            $package = $this->scopeConfig->getValue('design/package/name',
                ScopeInterface::SCOPE_STORE);
            $theme = $this->scopeConfig->getValue('design/theme/layout',
                ScopeInterface::SCOPE_STORE);
            $theme = ($theme == '') ? 'default' : $theme;

            //M1 > M2 Translation Begin (Rule p2-5.1)
            /*$config = Mage::getSingleton('core/design_config');
            $fallback = Mage::getSingleton('core/design_fallback', array(
                    'config' => $config)
                )
                ->getFallbackScheme($area, $package, $theme);
            foreach ($fallback as $parent) {
                if ($parent['_package'] == 'rwd') {
                    return true;
                }
            }
            */
            $config = $this->designConfig;
            $currentTheme = $this->themeProvider->getThemeById($theme);
            if ($currentTheme->getParentTheme() == 'rwd') {
                return true;
            }
            //M1 > M2 Translation End
            return false;
        } else {
            return false;   // magento go not included in the comparison
        }
    }

    public function getAddressesCollectionForTypeCount($type)
    {
        $validValues = array('invoice', 'delivery');
        if (in_array($type, $validValues) && $this->scopeConfig->isSetFlag('Epicor_Comm/address/force_type',
                ScopeInterface::SCOPE_STORE)) {
            $collection = $this->customerSessionFactory()->getCustomer()->getAddressesByType($type, true, true);
            return ($this->scopeConfig->getValue('Epicor_Comm/epicor_address_search_trigger/versionsavailable',
                    ScopeInterface::SCOPE_STORE) <= count($collection)) ? true : false;
        } else {
            $collection = $this->customerSessionFactory()->getCustomer()->getCustomAddresses(null, false, true);
            is_array($collection) ? $count = count($collection) : $count = $collection->count();
            return ($this->scopeConfig->getValue('Epicor_Comm/epicor_address_search_trigger/versionsavailable',
                    ScopeInterface::SCOPE_STORE) <= $count) ? true : false;
        }
    }

    public function getAddressesCollectionForType($type)
    {
        $validValues = array('invoice', 'delivery');
        if (in_array($type, $validValues) && $this->scopeConfig->isSetFlag('Epicor_Comm/address/force_type',
                ScopeInterface::SCOPE_STORE)) {
            return $this->customerSessionFactory()->getCustomer()->getAddressesByType($type);
        } else {
            return $this->customerSessionFactory()->getCustomer()->getAddressesCollection();
        }
    }

    public function getBrandSelectStores()
    {
        $website = $this->storeManager->getWebsite();

        /* @var $storesAvailable Mage_Core_Model_Resource_Store_Group_Collection */
        //M1 > M2 Translation Begin (Rule p2-1)
        //$storesAvailable = Mage::getModel('core/store_group')->addFieldToFilter('website_id', array('eq' => $website->getId()))
        //    ->addFieldToFilter('ecc_storeswitcher', array('eq' => true));
        $storesAvailable = $this->storeGroup->create()->getCollection()->addFieldToFilter('website_id', array('eq' => $website->getId()));
        //->addFieldToFilter('ecc_storeswitcher', array('eq' => true));
        //M1 > M2 Translation End
        $erpAccount = $this->getErpAccountInfo();
        $stores = array();

        if ($erpAccount) {
            foreach ($storesAvailable as $store) {
                /* @var $store Mage_Core_Model_Store_Group */
                $storeModel = $this->storeManager->getStore($store->getDefaultStoreId());
                /* @var $storeModel Epicor_Comm_Model_Store */
                if ($erpAccount->isValidForStore($storeModel)) {
                    $stores[$store->getId()] = $store;
                }
            }
        }

        return $stores;
    }

    public function findProductBySku(
        $sku,
        $uom = '',
        $sendMsq = true,
        $forceMsq = false,
        $msqQty = 1,
        $msqAtts = array()
    ) {
        $product = $this->catalogProductFactory->create();


        if (!empty($uom)) {
            $uomSeparator = $this->commMessagingHelper->getUOMSeparator();
            $productCodes[] = $sku . $uomSeparator . $uom;
        }

        $productCodes[] = $sku;

        foreach ($productCodes as $productCode) {
            if ($productId = $product->getIdBySku($productCode)) {
                $product->load($productId);
                break;
            }
        }
        $product->setDataSource('web');

        if ($product->isObjectNew()) {
            $product = $this->_findByOldSku($productCode);
        }

        if (!$product || $product->isObjectNew()) {
            $product = $this->_findByCpn($sku, $uom);
        }

        if ($sendMsq && ($forceMsq || !$product || $product->isObjectNew())) {
            $product = $this->_findByMsq($productCode, $product, $msqQty, $msqAtts);

            if (!$product && $forceMsq) {
                $product = $this->catalogProductFactory->create();
                $product->setSku($sku);
                $product->setEccUom($uom);
            }

            if ($forceMsq || ($product && $product->hasDataSource())) {
                $product->isObjectNew(false);
            }
        }

        $foundProduct = (!$product || $product->isObjectNew()) ? false : $product;
        return $foundProduct;
    }

    private function _findByOldSku($skuValue)
    {
        $foundProduct = false;
        $oldProds = $this->catalogResourceModelProductCollectionFactory->create()->addAttributeToFilter('ecc_oldskus',
            array('like' => '%' . $skuValue . '%'));

        if ($oldProds->count() > 0) {
            $product = $this->catalogProductFactory->create();
            $foundProduct = $product->load($oldProds->getFirstItem()->getId());
            $foundProduct->setDataSource('web');
        }

        return $foundProduct;
    }

    private function _findByCpn($skuValue, $uomValue = '')
    {
        $foundProduct = false;
        $erpAccount = $this->getErpAccountInfo();

        $collection = $this->commResourceCustomerSkuCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Customer_Sku_Collection */
        $collection->addFieldToFilter('sku', $skuValue);
        if ($erpAccount) {
            $collection->addFieldToFilter('customer_group_id', $erpAccount->getId());
        }

        if ($collection->count() == 0) {
            $collection = $this->commResourceCustomerSkuCollectionFactory->create();
            /* @var $collection Epicor_Comm_Model_Resource_Customer_Sku_Collection */
            $collection->addFieldToFilter('sku', $skuValue);
            $collection->addFieldToFilter('customer_group_id', 0);
        }

        if ($collection->count() > 0) {
            $product = $this->catalogProductFactory->create();
            /* @var $product Epicor_Comm_Model_Product */
            $foundProduct = $product->load($collection->getFirstItem()->getProductId());
            $foundProduct->setDataSource('web');
        }

        if (!empty($uomValue) && $foundProduct) {
            $uomSeparator = $this->commMessagingHelper->getUOMSeparator();
            $productCode = $foundProduct->getSku() . $uomSeparator . $uomValue;
            $product = $this->catalogProductFactory->create();
            /* @var $product Epicor_Comm_Model_Product */
            $foundProduct = $product->load($product->getIdBySku($productCode));
            $foundProduct->setDataSource('web');
        }

        return $foundProduct;
    }

    private function _findByMsq($skuValue, $product = null, $msqQty = 1, $msqAtts = array())
    {
        $foundProduct = false;
        if (!$product) {
            $product = $this->catalogProductFactory->create();
        }
        $product->setSku($skuValue);
        /* @var $product Epicor_Comm_Model_Product */

        if (!empty($msqAtts)) {
            $product->setMsqAttributes($msqAtts);
        }

        $msq = $this->commMessageRequestMsqFactory->create();
        /* @var $msq Epicor_Comm_Model_Message_Request_Msq */
        $msq->setTrigger('find_by_sku');

        if ($msq->isActive()) {
            $msq->addProduct($product, $msqQty);
            if ($msq->sendMessage()) {
                if ($product->getIsSalable()) {
                    $foundProduct = $product;
                    $foundProduct->setEccUom($product->getMsqMessageData()->getUnitOfMeasureCode());
                    $foundProduct->setDataSource('erp');
                }
            }
        }

        return $foundProduct;
    }

    /**
     * @return bool
     */
    public function isCpnInEmailAllowed()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CPN_IN_EMAILS);
    }

    /**
     * @param $productId
     * @return false|mixed
     */
    public function getCpn($productId)
    {
        $customerGroupid = $this->getCustomer()->getEccErpaccountId();

        $collection = $this->commResourceCustomerSkuCollectionFactory->create();

        $collection->addFieldToFilter('product_id', $productId);
        if ($customerGroupid) {
            $collection->addFieldToFilter('customer_group_id', $customerGroupid);
        }

        if ($collection->count() == 0) {
            $collection = $this->commResourceCustomerSkuCollectionFactory->create();
            $collection->addFieldToFilter('product_id', $productId);
            $collection->addFieldToFilter('customer_group_id', 0);
        }

        $collection->addFieldToSelect('sku');

        if ($collection->count() > 0) {
            $i = $collection->getItems();
            $result = array();
            foreach ($i as $r) {
                array_push($result, $r->getData('sku'));
            }

            return $result;
        }

        return false;
    }

    public function getSelectableStores()
    {
        $valid_branded_stores = array();
        $helper = $this;
        /* @var $helper Epicor_Comm_Helper_Data */
        $website = $this->storeManager->getWebsite()->getData();
        $company = $website['ecc_company'];
        $stores_for_website = $helper->getBrandSelectStores();

        $customerId = $this->customerSessionFactory()->getCustomer()->getId();
        $customer = $this->customerCustomerFactory->create()->load($customerId);
        $erpId = ($customer->getEccSupplierErpaccountId()) ? $customer->getEccSupplierErpaccountId() : $customer->getEccErpaccountId();
        $user_erp_info = $helper->getErpAccountInfo($erpId);
        /* @var $user_erp_info Epicor_Comm_Model_Customer_Erpaccount */

        if ($user_erp_info) {
            $user_brands = $user_erp_info->getUnserializedBrands();
            foreach ($user_erp_info->getChildAccounts('T') as $childTradeAccount) {
                /* @var $childTradeAccount Epicor_Comm_Model_Customer_Erpaccount */
                $user_brands = array_merge($user_brands, $childTradeAccount->getUnserializedBrands());
            }
        } else {
            $user_brands = array();
        }
        if ($user_brands) {
            //compare stores for website with stores user is allowed to access
            foreach ($stores_for_website as $key => $site_store) {
                $site_store->setCompany(empty($site_store['company']) ? $company : $site_store['company']);
                foreach ($user_brands as $brand) {

                    $brand['company'] = array_key_exists('company', $brand) ? $brand['company'] : $website['ecc_company'];
                    $brand['site'] = array_key_exists('site', $brand) ? $brand['site'] : $website['ecc_site'];
                    $brand['group'] = array_key_exists('group', $brand) ? $brand['group'] : $website['ecc_group'];
                    $brand['warehouse'] = array_key_exists('warehouse',
                        $brand) ? $brand['warehouse'] : $website['ecc_warehouse'];

                    //if brand info matches, show store, but if brand info empty, show all stores
                    if ($this->compareBrands($brand, $site_store->getData())) {
                        $valid_branded_stores[$key] = $site_store;
                    }
                }
            }

            return $valid_branded_stores;
        } else {
            return $helper->getBrandSelectStores();
        }
    }

    /**
     * Compare two brands and returns true for a match
     *
     * @param array $brand1
     * @param array $brand2
     * @return boolean
     */
    public function compareBrands($brand1, $brand2)
    {
        $match = true;
        foreach (array('company', 'site', 'group', 'warehouse') as $key) {
            if (($brand1[$key] && $brand2[$key] && $brand1[$key] != $brand2[$key])) {
                $match = false;
                break;
            }
        }
        return $match;
    }

    public function removeTaxLine($taxValue = 0)
    {
        $removeTax = false;
        $taxValue = $this->commMessagingHelper->removeCurrencyCodePrefixDP($taxValue);   // strip currency symbol if present

        $taxValue = ($taxValue == '0') ? '0.00' : $taxValue;
        if ($this->scopeConfig->getValue('sales/taxline/display_taxlines',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId())):  // check if tax is to be displayed
            if (!$this->scopeConfig->getValue('tax/sales_display/zero_tax',
                ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId())):
                if ($taxValue == '0.00'):
                    $removeTax = true;
                endif;
            endif;
        else:
            $removeTax = true;
        endif;
        return $removeTax;
    }

    /**
     * Gets current customer
     *
     * @return \Epicor\Comm\Model\Customer
     */
    public function getCustomer()
    {
        $customerSession = $this->customerSessionFactory();
        /* @var $customerSession \Magento\Customer\Model\Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer \Epicor\Common\Model\Customer */

        return $customer;
    }

    public function convertDateToIso8601($date)
    {
        $formatted_date = date(DATE_ATOM, strtotime($date));
        return $formatted_date;
    }

    public function erpDateConversion($date)
    {
        //This receives the UTC date in format CCYY-MM-DDTHH.MM.SS+00.00
        //Previously erp dates were convered using...
        // Mage::helper('epicor_common/data')->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        //If this format is reintroduced, the code can be reactivated
        //Now CCYY-MM-DDdate is converted as sent in message...

        $convertedDate = date('m-d-Y', strtotime($date));

        return $convertedDate;
    }

    public function getStringBetween($string, $start, $end)
    {

        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public function addCdataTags($string, $mainBlockTag, $startTagArray, $endTagArray)
    {

        //NB this has not been tested for group nodes (ie those with multiple occurrances)

        $workingBlock = preg_split('@(?=' . $mainBlockTag . ')@',
            $string);   // this keeps the delimiter, unlike explode which removes it

        foreach ($startTagArray as $key => $start) {                                                // loop through xml placing cdata tags within specified tags in array
            $noStartTagArray = explode($start, $workingBlock[1],
                2);                       // create a 2 element array to remove start tag

            $noEndTagArray = explode($endTagArray[$key], $noStartTagArray[1],
                2);                   // create array to remove end tag
            $noEndTagArray[0] = $start . "<![CDATA[" . $noEndTagArray[0] . "]]>" . $endTagArray[$key];   // add cdata tags to data and replace removed tags

            $noStartTagArray[1] = implode($noEndTagArray);                                  // replace data in nostarttagarray
            $workingBlock[1] = implode($noStartTagArray);                                   // replace data in working block
        }
        return implode($workingBlock);                                                      // return string with cdata tags embedded
    }

    public function aggregateLocationStockLevels($msq_locations_stock_level, $locations)
    {
        //filter locations supplied in msq
        $userLocationKeys = array();
        $freeStock = 0;
        foreach ($locations as $key => $value) {
            $userLocationKeys[$value] = $value;
        }
        $valid_locations = array_intersect_key($msq_locations_stock_level, $userLocationKeys);
        //aggregate valid locations

        return array_sum($valid_locations);
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param array $options
     */
    protected function updateCartProductCustomOptions(&$item, $options, $qty = 0)
    {
        $itemOptions = $item->getOptions();

        foreach ($itemOptions as $option) {
            if (strtolower($option->getCode()) == 'info_buyrequest') {
                $unserialized = $this->serializer->unserialize($option->getValue());
                $unserialized['qty'] += $qty;

                foreach ($options as $id => $value) {
                    $unserialized['options'][$id] = $value;
                }
                $option->setValue($this->serializer->serialize($unserialized));
            } else {
                if (isset($options[str_replace('option_', '', $option->getCode())])) {
                    $option->setValue($options[str_replace('option_', '', $option->getCode())]);
                }
            }
        }

        $item->setOptions($itemOptions);
    }

    public function UTCwithOffset($timestamp = null)
    {
        $new_timezone = $this->scopeConfig->getValue('general/locale/timezone',
            ScopeInterface::SCOPE_STORE);
        $newTimezone = new \DateTimeZone($new_timezone);

        $newTime = new \DateTime("now", $newTimezone);
        $timestamp ? $newTime->setTimestamp($timestamp) : null;
        //M1 > M2 Translation Begin (Rule 32)
        //$UTCDateTime = $this->commHelper->getLocalDate($newTime->getTimestamp(), 'yyyy-MM-ddTHH:mm:ssZ', true);
        $UTCDateTime = $this->getLocalDate($newTime->getTimestamp(), \IntlDateFormatter::LONG, true);
        //M1 > M2 Translation End

        $formatter = new \IntlDateFormatter(
            $this->_localeResolver->getLocale(),
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::FULL,
            $new_timezone,
            null,
            'yyyy-MM-dd'
        );
        $formatter->setPattern('yyyy-MM-dd HH:mm:ss Z'); //'2017-04-03T00:00:00+00:00';

        $UTCDateTime2 = $formatter->format($timestamp);
        $hoursOffsetFromGMT = $newTimezone->getOffset($newTime) / 3600;
        $newUTC = array();
        $newUTC[0] = ($hoursOffsetFromGMT >= 0) ? '+' : '-';
        if (is_int($hoursOffsetFromGMT)) {
            $newUTC[1] = str_pad(abs($hoursOffsetFromGMT), 2, "0",
                STR_PAD_LEFT);          // pad out single hours but remove + or - first
            $newUTC[3] = '00';
        } else {
            $time = explode('.', $hoursOffsetFromGMT);
            $newUTC[1] = str_pad($time[0], 2, "0", STR_PAD_LEFT);
            $newUTC[3] = 60 * (str_pad($time[1], 2, "0", STR_PAD_RIGHT)) / 100;        // convert fractions to minutes
        }
        $newUTC[2] = ':';
        ksort($newUTC);
        return substr_replace($UTCDateTime, implode('', $newUTC),
            -6);                   // replace +00:00 with valid value
    }

    /*
     * Sanitizes Data with htmlentities
     *
     * @param mixed $data
     * @return mixed $data
     */

    public function sanitizeData($data, $isComment = false)
    {
        if (is_array($data)) {
            foreach ($data as $index => $value) {
                $_isComment = false;
                if(in_array($index, $this->commentVariable)) {
                    $_isComment = true;
                }
                $data[$index] = $this->sanitizeData($value, $_isComment);
            }
        } elseif (is_object($data) && $data instanceof \Magento\Framework\DataObject) {
            $values = $data->getData();
            foreach ($values as $index => $value) {
                $data->setData($index, $this->sanitizeData($value));
            }
        } elseif (is_string($data)) {
            if ($isComment == true) {
                return $data;
            } else {
                //$data = htmlentities($data, ENT_QUOTES, "UTF-8");
                $data = $this->stripNonPrintableChars($data);
            }
        }

        return $data;
    }

    public function addressValidate($address, $returnErrors = false)
    {
        // check length limits
        $use_length_limits = $this->scopeConfig->isSetFlag('customer/address/limits_enabled',
            ScopeInterface::SCOPE_STORE);
        if ($use_length_limits) {
            $name_length_limit = $this->scopeConfig->getValue('customer/address/limit_company_length',
                ScopeInterface::SCOPE_STORE) ?: 10234;
            $address_length_limit = $this->scopeConfig->getValue('customer/address/limit_address_line_length',
                ScopeInterface::SCOPE_STORE) ?: 10234;
            $telephone_length_limit = $this->scopeConfig->getValue('customer/address/limit_telephone_length',
                ScopeInterface::SCOPE_STORE) ?: 10234;
            $instructions_length_limit = $this->scopeConfig->getValue('customer/address/limit_instructions_length',
                ScopeInterface::SCOPE_STORE) ?: 10234;

            if (array_key_exists('street',
                $address)) {                                   // needed because the street fields have been merged into one field by this point
                if (is_array($address['street'])) {
                    foreach ($address['street'] as $key => $street) {
                        $address['street_' . $key] = $street;
                    }
                }
            }

            $nameArray = array(// register, rfq
                array('code' => 'name', 'limit_name' => 'name_length_limit', 'message_prefix' => 'Name')
            ,
                array(
                    'code' => 'address1',
                    'limit_name' => 'address_length_limit',
                    'message_prefix' => 'Address line 1'
                )
            ,
                array(
                    'code' => 'address2',
                    'limit_name' => 'address_length_limit',
                    'message_prefix' => 'Address line 2'
                )
            ,
                array(
                    'code' => 'address3',
                    'limit_name' => 'address_length_limit',
                    'message_prefix' => 'Address line 3'
                )
            ,
                array('code' => 'telephone', 'limit_name' => 'telephone_length_limit', 'message_prefix' => 'Telephone')
            ,
                array('code' => 'phone', 'limit_name' => 'telephone_length_limit', 'message_prefix' => 'Telephone')
            ,
                array(
                    'code' => 'mobile_number',
                    'limit_name' => 'telephone_length_limit',
                    'message_prefix' => __('Mobile')
                )
            ,
                array('code' => 'fax_number', 'limit_name' => 'telephone_length_limit', 'message_prefix' => 'Fax')
            ,
                array(
                    'code' => 'instructions',
                    'limit_name' => 'instructions_length_limit',
                    'message_prefix' => 'Instructions'
                )
            );
            foreach ($nameArray as $name) {
                $length_limit = ${$name['limit_name']};
                if (array_key_exists($name['code'], $address) && $length_limit != 10234) {
                    if (strlen($address[$name['code']]) > $length_limit) {
                        $errors[] = __("{$name['message_prefix']} cannot exceed {$length_limit} chars");
                    }
                }
            }

            if (!empty($errors)) {
                if ($returnErrors) {
                    return implode('<br>', $errors);
                } else {
                    foreach ($errors as $error) {
                        if ($this->customerSessionFactory()->isLoggedIn()) {
                            $this->genericFactory->create()->addError(__($error));   // for popup
                        } else {
                            $this->customerSessionFactory()->addError(__($error));  // for registration
                        }
                    }
                    $isAjax = $this->request->getParam('isAjax');
                    if ($isAjax) {
                        //M1 > M2 Translation Begin (Rule p2-4)
                        //echo json_encode(array('redirect' => Mage::getUrl('customerconnect/account/'), 'type' => 'success'));     // for ajax popups
                        echo json_encode(array(
                            'redirect' => $this->_getUrl('customerconnect/account/'),
                            'type' => 'success'
                        ));     // for ajax popups
                        //M1 > M2 Translation End
                    } else {
                        //M1 > M2 Translation Begin (Rule p2-3)
                        //Mage::app()->getResponse()->setRedirect($this->request->getServer('HTTP_REFERER'))->sendResponse();
                        $this->response->setRedirect($this->request->getServer('HTTP_REFERER'))->sendResponse();
                        //M1 > M2 Translation End
                    }
                    exit;
                }
            }
        }
    }

    public function getFlattenedAddress($address)
    {
        $fields = array(
            'street',
            'address1',
            'address2',
            'address3',
            'city',
            'county',
            'country',
            'postcode',
            'telephone_number',
            'mobile_number',
            'fax_number'
        );

        $dataArray = array();
        foreach ($fields as $field) {
            $data = $address->getData($field);
            if ($data) {
                $dataArray[] = $data;
            }
        }

        return join(', ', $dataArray);
    }

    public function retrieveContractTitle($contractCode)
    {
        if (!$this->erpAccount) {
            $this->erpAccount = $this->getErpAccountInfo();
        }
        if (!$this->erpAccount) {
            return $contractCode;
        }
        $contractModel = $this->listsListModelFactory->create()->load($this->erpAccount->getAccountNumber() . $this->commMessagingHelper->getUOMSeparator() . $contractCode,
            'erp_code');
        return $contractModel->isObjectNew() ? null : $contractModel->getTitle();
    }

    public function retrieveContractTitle2($contractCode)
    {
        $contractModel = $this->listsListModelFactory->create()->load($contractCode);
        return $contractModel->isObjectNew() ? null : $contractModel->getTitle();
    }


    /**
     * Get all stores that match the company branding
     * null values are wildcards.
     * @param string $company
     * @return array
     */
    public function getStoresFromCompanyBranding($company)
    {
        $stores = array();
        $mage_stores = $this->storeManager->getStores();
        foreach ($mage_stores as $store) {

            $store_company = $store->getWebsite()->getEccCompany() ?: $store->getGroup()->getEccCompany();

            if (($store_company == null || $company == null || $store_company == $company)) {
                $stores[$store->getId()] = $store;
            }
        }

        return $stores;
    }

    /**
     * Returns the additional value that is allowed to be charged on the given order based on the payment method.
     * @param \Magento\Sales\Model\Order $order
     */
    public function getErpAccountId()
    {
        $session = $this->customerSessionFactory();
        $customer = $session->getCustomer();
        if ($customer->getId()) {
            $erpAccountId = $customer->getEccErpaccountId();
            if ($customer->isguest() && !$customer->getEccErpaccountId()) {
                $erpAccountId = $this->scopeConfig->getValue('customer/create_account/default_erpaccount',
                    ScopeInterface::SCOPE_STORE);
            }
        } else {
            $erpAccountId = $this->scopeConfig->getValue('customer/create_account/default_erpaccount',
                ScopeInterface::SCOPE_STORE);
        }
        return $erpAccountId;
    }

    /*
     * return array of all valid ecc attribute types
     */

    public function _getEccattributeTypes($returnOption = true)
    {
        $array = array(
            '' => '--- Select Option ---'
        ,
            'select' => 'Dropdown'
        ,
            'text' => 'Text'
        ,
            'textarea' => 'Textarea'
        ,
            'date' => 'Date'
        ,
            'boolean' => 'Yes/No'
        ,
            'multiselect' => 'Multi Select'
        ,
            'price' => 'Price'
        ,
            'media_image' => 'Media Image'
        ,
            'weee' => 'Fixed Product Tax'
        );
        if (!$returnOption) {
            array_shift($array);
        }
        return $array;
    }

    /*
     * import data to update epicor_comm/erp_mapping_attributes from CSV
     */

    public function importAttributeMappingFromCsv($file)
    {
        $list = $this->commErpMappingAttributesFactory->create();
        /* @var $list Epicor_Lists_Model_ListModel */
        $errors = array();
        $success = array();
        $fileContents = fopen($file, "rb");
        if (!$fileContents) {
            $errors[] = __('Could not process file properly, please try again.');
        }
        $tableColumns = $this->commResourceErpMappingAttributesFactory->create()->getFields();
        unset($tableColumns['id']);
        $tableColumnKeys = array_keys($tableColumns);
        $flippedTableColumnKeys = array_flip($tableColumnKeys);
        $attributeTypes = $this->_getEccattributeTypes(false);
        $rowArrayKey = '';
        $abort = false;

        while (!feof($fileContents)) {
            $row = trim(fgets($fileContents));
            if (strpos($row, '###') === 0 || $row == false || $abort) {
                continue;
            }
            //replace "," in row or it will mess up explode
            $row = str_replace('","', 'comma', strtolower(trim($row)));
            //compare supplied headers with available
            $row = str_replace(' ', '_', strtolower(trim($row)));
            $rowArray = explode(',', $row);
            $abort = false;
            if (in_array('attribute_code', $rowArray)) {
                $rowArrayColumns = $rowArray;
                $rowArrayKey = array_flip($rowArray);
                $incorrectColumns = array_diff_key($flippedTableColumnKeys, $rowArrayKey);
                foreach ($incorrectColumns as $key => $incorrectColumn) {
                    $errors[] = __("Column Header '{$key}' Not Supplied. Cannot Continue");
                    $abort = true;
                }
                continue;
            }
            if (empty($rowArrayColumns)) {
                $errors[] = __("Column Headers Must be Supplied. Cannot Continue");
                $abort = true;
                continue;
            }

            if (!$abort) {
                // process regular rows
                $model = $this->commErpMappingAttributesFactory->create()->load($rowArray[$rowArrayKey['attribute_code']],'attribute_code');
                $error = '';
                $yesnoArray = array(
                    'is_visible_in_advanced_search',
                    'is_searchable',
                    'is_comparable',
                    'is_filterable',
                    'is_filterable_in_search',
                    'is_used_for_promo_rules',
                    'is_html_allowed_on_front',
                    'is_visible_on_front',
                    'used_in_product_listing',
                    'used_for_sort_by'


                );
                foreach ($rowArray as $key => $column) {
                    if (in_array($rowArrayColumns[$key], $yesnoArray)) {

                        // if not supplied set to N
                        $column = strtoupper($column ? $column : 'N');

                        //if not valid, error
                        if (!in_array($column, array('Y', 'N', '1', '0', 'TRUE', 'FALSE'))) {
                            $error = "Boolean attribute '{$rowArray[$rowArrayKey['attribute_code']]}', column '{$rowArrayColumns[$key]}'  is not Y or N. Cannot Process Attribute";
                        } else {
                            //set to true, if positive value
                            $column = in_array($column, array('Y', '1', 'TRUE')) ? true : false;
                        }
                    }
                    switch ($rowArrayColumns[$key]) {
                        //only save separator if multiselect
                        case 'input_type':
                            $column = $column ? $column : 'text';
                            if (!array_key_exists($column, $attributeTypes)) {
                                $error = "Supplied Attribute Type '{$rowArray[$rowArrayKey['input_type']]}' is Invalid. Attribute Not Processed Attribute.";
                                break 2;
                            }
                            break;
                        case 'separator':
                            if ($rowArray[$rowArrayKey['input_type']] != 'multiselect') {
                                $column = null;
                            } else {
                                if ($column) {
                                    $column = (strtolower($column) == 'comma') ? ',' : $column;
                                } else {
                                    $error = "Multiselect attribute '{$rowArray[$rowArrayKey['attribute_code']]}' has no separator. Cannot Process Attribute";
                                    break 2;
                                }
                            }
                            break;
                        case 'position':
                        case 'search_weight':
                            // set to 1 if not supplied
                            $column = $column ? $column : 1;
                            if ($column < 0) {
                                $error = "Attribute '{$rowArray[$rowArrayKey['attribute_code']]}', column {$rowArrayColumns[$key]} is less than zero . Cannot Process Attribute";
                                break 2;
                            }
                            break;
                        case 'is_filterable':
                            // set to 0 if not supplied
                            $column = $column ? $column : 0;
                            if (!in_array($column, array(0, 1, 2))) {
                                $error = "Attribute '{$rowArray[$rowArrayKey['attribute_code']]}', column 'is_filterable' value must be: 0 - No, 1 - Filterable (with results) or 2 - Filterable (no results) . Cannot Process Attribute";
                                break 2;
                            }
                            break;
                        default:
                            break;
                    }


                    $model->setData($rowArrayColumns[$key], $column);
                }
                if (empty($error)) {
                    $model->save();
                    $success[] = "Attribute Code '{$model->getAttributeCode()}' on Epicor_Comm_Erp_Attributes has been " . ($model->isObjectNew() ? ' Added' : ' Updated');
                } else {
                    $errors[] = $error;
                }
            }
        }
        fclose($fileContents);
        return ['errors'=>$errors,'success'=>$success];
    }

    public function getCustomerSession()
    {
        return $this->customerSessionFactory;
    }

    public function isModuleEnabled($moduleName)
    {
        return $this->_moduleManager->isEnabled($moduleName);
    }

    /**
     * Get the store configuration value
     *
     * @param string $config_path
     * @return mixed
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get All Message type which supports Array
     *
     *
     * @return Array
     */
    public function getArrayMessages()
    {
        return $this->arrayMessages->getType();
    }
    /*
     * send email when site is set to offline
     */
    public function sendEmailWhenSiteOffline(){
        $storeId = $this->storeManager->getStore()->getId();
        $setting = $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/email_alert_site_offline', ScopeInterface::SCOPE_STORE, $storeId);
        // $setting = $this->scopeConfig->getValue('Epicor_Comm/xmlMessaging/failed_msg_online', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $to = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/email', ScopeInterface::SCOPE_STORE);
        $name = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/name', ScopeInterface::SCOPE_STORE);
        /** @var $model Mage_Core_Model_Email_Template */
        $vars = array(
            'adminname' => $name,
        );

        $templateOptions = ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId()];
        $mail = $this->transportBuilder->setTemplateIdentifier('epicor_comm_email_alerts_admin_site_offline_template')
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($vars)
            ->setFrom($setting)
            ->addTo($to)
            ->getTransport();
        $mail->sendMessage();
    }

    /*
     * save customer custom attribute values while account creation/cuco processing.
     */
    public function saveCustomerInfo($customer, $erpAccountId, $delAddresses = false) {

        $customer->save();

        $customerRepository = $this->customerRepository->getById($customer->getId());
        $customerRepository->setCustomAttribute('ecc_function', $customer->getData('ecc_function'));
        $customerRepository->setCustomAttribute('ecc_telephone_number', $customer->getData('ecc_telephone_number'));
        $customerRepository->setCustomAttribute('ecc_fax_number', $customer->getData('ecc_fax_number'));
        $customerRepository->setCustomAttribute('email', $customer->getData('email'));
        $customerRepository->setCustomAttribute('ecc_per_contact_id', $customer->getData('ecc_per_contact_id'));
        //$customerRepository->setCustomAttribute('ecc_erpaccount_id', $erpAccountId);
        //$customerRepository->setCustomAttribute('ecc_erp_account_type', 'customer');
        //$customerRepository->setCustomAttribute('ecc_contact_code', $customer->getEccContactCode());
        $extensionAttributes = $customerRepository->getExtensionAttributes(); /** get current extension attributes from entity **/
        $extensionAttributes->setEccMultiErpId($erpAccountId);
        $extensionAttributes->setEccMultiContactCode($customer->getEccContactCode(true));
        $extensionAttributes->setEccMultiErpType('customer');
        $customerRepository->setExtensionAttributes($extensionAttributes);
        $customerRepository->setCustomAttribute('ecc_master_shopper', $customer->getEccMasterShopper());
        if ($customer->getEccHidePrice() != "") {
            $customerRepository->setCustomAttribute('ecc_hide_price', $customer->getEccHidePrice());
        }
        $customerRepository->setCustomAttribute('ecc_access_roles', $customer->getEccAccessRoles());
        $customerRepository->setCustomAttribute('ecc_access_rights', $customer->getEccAccessRights());
        $customerRepository->setCustomAttribute('ecc_is_toggle_allowed', $customer->getEccIsToggleAllowed());
        $customerRepository->setCustomAttribute('ecc_login_mode_type', $customer->getEccLoginModeType());
        $customerRepository->setCustomAttribute('ecc_cuco_pending', $customer->getEccCucoPending());
        $customerRepository->setCustomAttribute('ecc_erp_login_id', $customer->getEccErpLoginId());
        $customerRepository->setCustomAttribute('ecc_location_link_type', $customer->getEccLocationLinkType());

        $this->customerRepository->save($customerRepository);

        //Delete all customer addresses when Guest to B2B conversion
        if($delAddresses){
            $this->eventManager->dispatch(
                'ecc_cuco_del_addresses', ['customer' => $customer]
            );
        }

        $this->eventManager->dispatch(
            'ecc_cuco_save_after', ['customer' => $customer]
        );
        $this->registry->unregister('updating_erp_address');
    }

    /**
     * Gets the next available web ref for CRRU and increment the counter
     *
     * @return integer;
     */
    public function getNextReturnWebRef()
    {
        $webRef = $this->scopeConfig->getValue('customerconnect/return/increment');
        if (!$webRef) {
            $webRef = 0;
        }
        $webRef++;
        $store = $this->storeManager->getStore()->getId();
        $this->resourceConfig->saveConfig(
            'customerconnect/return/increment', $webRef, 'default', 0
        );
        $this->storeManager->getStore($store)->resetConfig();
        return $webRef;
    }
    /**
     * Gets the character limits value against the affected fields in an array format
     *
     * @return array;
     */
    public function getLimitOptions($fieldArr)
    {
        if (!$fieldArr) {
            return array('email', 'name', 'lastname', 'company',  'postcode', 'address_line', 'telephone');
        } else {
            return array('_name' => 'name',
                'firstname' => 'name',
                'lastname' => 'lastname',
                'company' => 'company',
                'ecc_email' => 'email',
                'street' => 'address_line',
                'telephone' => 'telephone',
                'ecc_mobile_number' => 'telephone',
                'fax' => 'telephone',
                'zip' => 'postcode',
                'postcode' => 'postcode');
        }
    }

    /**
     * Returns Configurator Values
     *
     * @return array
     */
    public function ewaConfiguratorValues() {

        return array(
            array('value' => '', 'label' => ''),
            array('value' => 'ewa_title', 'label' => 'Configured Title'),
            array('value' => 'ewa_sku', 'label' => 'Configured SKU'),
            array('value' => 'ewa_description', 'label' => 'Configured Description'),
            array('value' => 'ewa_short_description', 'label' => 'Configured Short Description'),
        );
    }

    /**
     *
     * @param \Magento\Sales\Model\Order $order
     * @return boolean
     */
    public function isGorRetry($order){

        $retryCount = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/retry_count', ScopeInterface::SCOPE_STORE) ?: 0;
        $gorCount = $order->getEccGorSentCount();

        $agedTimeSecond = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/aged_time_limit', ScopeInterface::SCOPE_STORE) ?: 0;
        $currentDateTime = date('Y-m-d H:i:s');
        $agedDateTime = date('Y-m-d H:i:s',strtotime("-$agedTimeSecond second"));
        $orderCreateAt = $order->getCreatedAt();
        $incrementId = $order->getIncrementId();

        if (($retryCount && $gorCount >= $retryCount) || ($agedTimeSecond && strtotime($orderCreateAt) <= strtotime($agedDateTime))) {
            $isEmailNotification = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/error_email_notifcation', ScopeInterface::SCOPE_STORE);

            $ownerEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
            $ownerName = $this->scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);
            $emailDesc = "Retry attempt failure on GOR for Order #$incrementId at $currentDateTime as either of the aged time limit or retry attempt limit has reached.";
            /** @var $model Mage_Core_Model_Email_Template */
            $vars = [   'adminName' => $ownerName,
                'adminEmail' => $ownerEmail,
                'contentMsg' => "GOR not sent -- Error - Retry Attempt Failure",
                'contentDesc' => $emailDesc,
            ];
            $from = [   'email' => $ownerEmail,
                'name' => $ownerName
            ];

            if($isEmailNotification){
                $this->sendTransactionalEmail('epicor_comm_message_error_email_template', $from, $ownerEmail, $ownerName, $vars, $storeId = null);
            } else {
                $this->_logger->error($emailDesc);
            }
            if($retryCount && $gorCount >= $retryCount){
                $order->setEccGorMessage('GOR not sent -- Error - Retry Attempt Failure --- Retry Count Limit Reached');
            }
            elseif($agedTimeSecond && strtotime($orderCreateAt) <= strtotime($agedDateTime)){
                $order->setEccGorMessage('GOR not sent -- Error - Retry Attempt Failure --- Aged Time Limit Reached');
            }
            return false;
        }

        return true;
    }

    public function getCommMessagingHelper()
    {
        return $this->commMessagingHelper;
    }

    /**
     * validate E10 ERP send message request
     * in to REST FORMATE or not
     *
     * @return bool
     */
    public function isEnableRest()
    {
        $scopeConfig = $this->getScopeConfig();
        $isEnableRest = $scopeConfig->getValue("Epicor_Comm/xmlMessaging/userest",
            ScopeInterface::SCOPE_STORE);
        $ERPType = $scopeConfig->getValue('Epicor_Comm/licensing/erp',
            ScopeInterface::SCOPE_STORE);
        if ($isEnableRest && $ERPType == "e10") {
            return true;
        }

        return false;
    }

    /*
    * check if guest to B2B conversion emails to be sent to customer or not
    */
    public function canSendConversionEmail()
    {
        $regPortal = $this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_portal', ScopeInterface::SCOPE_STORE);
        $option = $this->scopeConfig->isSetFlag('epicor_b2b/registration/b2b_acct_type', ScopeInterface::SCOPE_STORE);
        $sendWelcomeEmail = $this->scopeConfig->isSetFlag('epicor_b2b/registration/enable_guest_to_b2b_welcome_email', ScopeInterface::SCOPE_STORE);

        if ($regPortal && $option == 'guest_acct' && $sendWelcomeEmail) {
            return true;
        }

        return false;
    }

    public function sendConversionEmail($customer)
    {
        $customerData = [];
        $name = $customer->getFirstname()." ".$customer->getLastname();
        $templateId = $this->scopeConfig->getValue('epicor_b2b/registration/guest_to_b2b_welcome_email_template', ScopeInterface::SCOPE_STORE);
        $customerERPData = $customer->getCustomerErpAccount();
        $customerData['customer_email'] = $customer->getEmail();
        $customerData['fullname'] = $name;
        $customerData['account_name'] = $customerERPData->getName();
        $this->_sendEmail($templateId, $customerData, $customer->getEmail(), $customerData['fullname']);
    }

    public function _sendEmail($templateId, $vars, $to, $name)
    {
        try {
            if (isset($vars['region_id'])) {
                if ($vars['region_id'] != '') {
                    $vars['state_code'] = $this->directoryRegionFactory->create()->load($vars['region_id'])->getName();
                } else {
                    $vars['state_code'] = $vars['region'];
                }
            }
            $translate = $this->translateInterface;
            /* @var $translate \Magento\Framework\Translate\Inline\StateInterface */
            $translate->suspend(false);

            $storeId = $this->storeManager->getStore()->getId();

            $from = $this->scopeConfig->getValue('customer/create_account/email_identity', ScopeInterface::SCOPE_STORE);

            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
                ->setTemplateVars($vars)
                ->setFrom($from)
                ->addTo($to, $name)
                ->getTransport();

            $transport->sendMessage();
            $translate->resume(true);

        } catch (\Exception $e) {
            $translate->resume(true);
        }

    }

    /**
     * Ship Status
     *
     * @param null $custType
     * @param int $storeId
     * @return bool
     */
    public function isShipStatus($custType = null, $storeId = 0){
        if ($custType) {
            $custType = $custType;
        } else {
            /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
            $erpAccount = $this->getErpAccountInfo();
            $custType =$erpAccount->getAccountType();

        }
        $scopeConfig = $this->getScopeConfig();
        $isEnableShipStatus = $scopeConfig->getValue("checkout/options/ship_status",
            ScopeInterface::SCOPE_STORE);
        switch ($isEnableShipStatus) {
            case '':
                return false;
                break;
            case 'yes':
                return true;
                break;
            case 'b2b':
                if ($custType == "B2B") {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'b2c':
                if ($custType == "B2C") {
                    return true;
                } else {
                    return false;
                }
                break;
        }
        return false;
    }

    /**
     * get Shipping Status Collection available by default or erp account mapped
     *
     * @return \Epicor\Comm\Model\Erp\Mapping\Shippingstatus|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getShipStatusCollection() {

        $collection = null;
        /* @var $erpAccount \Epicor\Dealerconnect\Model\Customer\Erpaccount */
        $erpAccount = $this->getErpAccountInfo();
        $storeId = $this->storeManager->getStore()->getId();
        $erpMappedAcountCodes = unserialize($erpAccount->getAllowedShipstatusMethods());
        $erpAccountCodes1 = ($erpMappedAcountCodes) ? $erpMappedAcountCodes : array();
        /* @var $shippingStatusMapping \Epicor\Comm\Model\Erp\Mapping\Shippingstatus */
        $shippingStatusMapping = $this->commErpMappingShippingstatusFactory->create();

        $erpAccountCodes2 = $shippingStatusMapping->getDefaultErpshipstatus();
        $erpAccountCodes = array_merge($erpAccountCodes1, $erpAccountCodes2);
        /* check if customer has mapped status code and if it is default and belongs to current store validation */
        if ($erpAccountCodes) {

            $erpStatusCode = array();
            foreach ($erpAccountCodes as $erpaccount) {
                $erpStoreId = $shippingStatusMapping->load($erpaccount, 'shipping_status_code')->getStoreId();
                if ($erpStoreId == $storeId || $erpStoreId == 0) {
                    $erpStatusCode[] = $erpaccount;
                }
            }
            if ($erpStatusCode) {
                $collection = $shippingStatusMapping->getErpCodes($erpStatusCode);
            }
        } else {
            $store = array($storeId, 0);
            $collection = $shippingStatusMapping->getIdByStore($store);
        }
        return $collection;
    }

    /**
     * Required customer shipping Date
     *
     * @param string|null $custType
     * @param int $storeId
     * @return bool
     */
    public function isRequiredDate($custType = null, $storeId = 0) {
        if ($custType) {
            $custType = $custType;
        } else {
            /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
            $erpAccount = $this->getErpAccountInfo();
            $custType =$erpAccount->getAccountType();

        }
        $scopeConfig = $this->getScopeConfig();
        $isEnableRequiredDate = $scopeConfig->getValue("checkout/options/required_date",
            ScopeInterface::SCOPE_STORE);
        switch ($isEnableRequiredDate) {
            case '':
                return false;
                break;
            case 'yes':
                return true;
                break;
            case 'b2b':
                if ($custType == "B2B") {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'b2c':
                if ($custType == "B2C") {
                    return true;
                } else {
                    return false;
                }
                break;
        }
        return false;
    }

    /**
     * @param string|null $custType
     * @param int $storeId
     * @return bool
     */
    public function isEccAdditionalReference($custType = null, $storeId = 0)
    {
        if ($custType) {
            $custType = $custType;
        } else {
            /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
            $erpAccount = $this->getErpAccountInfo();
            $custType = $erpAccount->getAccountType();

        }
        $scopeConfig = $this->getScopeConfig();
        $storeConfigValue = $scopeConfig->getValue("checkout/options/ecc_additional_reference",
            ScopeInterface::SCOPE_STORE);
        switch ($storeConfigValue) {
            case '':
                return false;
                break;
            case 'yes':
                return true;
                break;
            case 'b2b':
            case 'b2bm':
                if ($custType == "B2B") {
                    return true;
                }
                break;
            case 'b2c':
            case 'b2cm':
                if ($custType == "B2C") {
                    return true;
                }
                break;
            case 'm':
                return true;
                break;
        }
        return false;
    }

    /**
     * check mandate Additional Reference
     *
     * @param string|null $custType
     * @param int $storeId
     * @return bool
     */
    public function isMandatoryEccAdditionalReference($custType = null, $storeId = 0)
    {
        if ($custType) {
            $custType = $custType;
        } else {
            /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
            $erpAccount = $this->getErpAccountInfo();
            $custType = $erpAccount->getAccountType();

        }
        $scopeConfig = $this->getScopeConfig();
        $storeConfigValue = $scopeConfig->getValue("checkout/options/ecc_additional_reference",
            ScopeInterface::SCOPE_STORE);
        switch ($storeConfigValue) {
            case '':
            case 'yes':
                return false;
                break;
            case 'b2b':
            case 'b2c':
                if ($custType == "B2B") {
                    return false;
                } else {
                    return false;
                }
                break;
            case 'b2bm':
                if ($custType == "B2B") {
                    return true;
                }
                break;
            case 'b2cm':
                if ($custType == "B2C") {
                    return true;
                }
                break;
            case 'm':
                return true;
                break;
        }
        return false;
    }

    /**
     * Additional Reference get max length
     * @return bool|string
     */
    public function getAReferenceMaxLength()
    {
        $scopeConfig = $this->getScopeConfig();
        $storeConfigValue = $scopeConfig->getValue("checkout/options/ecc_additional_reference_length",
            ScopeInterface::SCOPE_STORE);
        return $storeConfigValue ?: false;
    }

    /**
     * @return mixed
     */
    public function getMappingShippingstatusFactory(){
        return $this->commErpMappingShippingstatusFactory->create();
    }

    /**
     * Get the value of show/hide prices option from configuration to populate the field in contact create/update form
     *
     * @return bool
     */
    public function isHidePriceEnabledForContact(){

        $canHidePrice = false;
        $customerSession = $this->customerSessionFactory();
        /* @var $customerSession Mage_Customer_Model_Session */
        if (!$customerSession->isLoggedIn()) {
            return $canHidePrice;
        }
        $customer = $customerSession->getCustomer();
        $erpAccountId = $customer->getEccErpaccountId();
        $erpAccount = $this->commHelper->getErpAccountInfo();
        $erpAccountHidePrices = $erpAccount->getHidePriceOptions();
        $gloablHidePrices = $this->scopeConfig->getValue('customer/contact_hide_prices/active', ScopeInterface::SCOPE_STORE);

        if(!$erpAccount->isTypeB2b() || empty($erpAccountId) || $erpAccountHidePrices == "0" || ($erpAccountHidePrices == "" && $gloablHidePrices == "0")){
            return $canHidePrice;
        }
        if($erpAccountHidePrices || ($erpAccountHidePrices == "" && $gloablHidePrices == "1")){
            $canHidePrice = true;
        }

        return $canHidePrice;
    }

    /**
     * ecc_hide_price enabled for customer?
     *
     * @return bool
     */
    public function getEccHidePrice()
    {
        $customerSession = $this->customerSessionFactory();
        /* @var $customerSession Mage_Customer_Model_Session */
        if (!$customerSession->isLoggedIn()) {
            return false;
        }
        $customer = $customerSession->getCustomer();
        $erpAccountId = $customer->getEccErpaccountId();
        $erpAccount = $this->commHelper->getErpAccountInfo();
        if(!$erpAccount->isTypeB2b() || empty($erpAccountId) || $customer->getEccHidePrice() == 0){
            return false;
        }

        return $customer->getEccHidePrice();
    }

    /**
     * get Default ERP Account Number
     *
     */
    public function getDefaultAccount($erpAccountId)
    {
        $erpAccount = $this->getErpAccountInfo($erpAccountId);
        return $erpAccount->getAccountNumber();
    }

    public function getDefaultShippingAddress($erpAccountId)
    {
        $erpAccountInfo = $this->getErpAccountInfo($erpAccountId);
        $customer = $this->customerSessionFactory()->getCustomer();
        $defaultShippingAddressCode = $erpAccountInfo->getDefaultDeliveryAddressCode();
        $shippingAddress = $erpAccountInfo->getAddress($defaultShippingAddressCode);
        $shippingAddress = $shippingAddress->toCustomerAddress($customer, $erpAccountId);

        return $shippingAddress;
    }

    public function getCustomerAddressOptions()
    {
        $mobReq = $this->scopeConfig->isSetFlag('customer/address/mobile_number_required', ScopeInterface::SCOPE_STORE);
        return array(
            'ecc_mobile_number' => ['name' => 'display_mobile_phone', 'req' => $mobReq],
            'ecc_email' => ['name' => 'display_email']
        );
    }

    /**
     * check for tax exempt allowed
     * @method isTaxExemptionAllowed
     * @param $storeId,$customerErpAccountNo
     * @return bool
     */
    public function isTaxExemptionAllowed()
    {
        $erpModel = $this->getErpAccountInfo();
        //check for customerAccount level enabled or not if global default take the global else take the checkout config
        $customerErpAllow = $erpModel->getIsTaxExempt();
        if ($customerErpAllow == 2 || $customerErpAllow == NULL) {
            $allow = $this->scopeConfig->getValue('checkout/options/allow_tax_exempt', ScopeInterface::SCOPE_STORE);
        } elseif ($customerErpAllow == 0) {
            $allow = false;
        } elseif ($customerErpAllow == 1) {
            $allow = true;
        }
        return $allow;
    }

    public function handleOutofStock($erpProduct, $eccProduct, $configurator)
    {
        $remove = $this->registry->registry('hide_out_of_stock_product') ? : [];
        $productSku = $eccProduct->getSku();
        $productConfigurator[$productSku] = $eccProduct->getConfigurator() ? : $configurator;
        $productId[$productSku] = $eccProduct->getEntityId();
        $productType[$productSku] = $eccProduct->getTypeId();

        $customerLocations = $eccProduct->getCustomerLocations();
        $singleLocation = count($customerLocations) == 1;
        if ($singleLocation) {
            $location = array_pop($customerLocations);
            $eccProduct->setToLocationPrices($location);
        }

        if (($productType[$productSku] == 'simple'
                || $productType[$productSku] == 'bundle' )
            && $productConfigurator[$productSku] != 1
            && ($erpProduct['status']['code'] == '011'
                || $erpProduct['freeStock'] <= $this->scopeConfig->getValue('cataloginventory/options/stock_threshold_qty', ScopeInterface::SCOPE_STORE))) {
            if (!in_array($eccProduct->getId(), $remove)) {
                $remove[] = $eccProduct->getId();
            }
        }
        $this->registry->unregister('hide_out_of_stock_product');
        $this->registry->register('hide_out_of_stock_product', array_unique($remove));
    }

    /**
     * Display out of stock products option
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isShowOutOfStock()
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get associated products after out of stock filter
     *
     * @param \Magento\Catalog\Model\Product $_product
     * @return array
     */
    public function getAssociatedProducts($_product)
    {
        $_associatedProducts = $_product->getTypeInstance()->getAssociatedProducts($_product);
        if (!$this->isShowOutOfStock()) {
            $_associatedProducts = !empty($_associatedProducts) ? array_filter($_associatedProducts, function ($arrayValue) {
                return $arrayValue->isSaleable();
            }) : [];
        }
        return $_associatedProducts;
    }

    /**
     * @param int $erpAccountId
     * @param int $addressId
     * @return Address
     */
    public function getSalesrepAddress($erpAccountId, $addressId)
    {
        $erpAccountInfo = $this->getErpAccountInfo($erpAccountId);
        $customer = $this->customerSessionFactory()->getCustomer();
        $shippingAddress = $erpAccountInfo->getAddressById($addressId);
        $shippingAddress = $shippingAddress->toCustomerAddress($customer, $erpAccountId);

        return $shippingAddress;
    }

    /**
     * @param int $erpAccountId
     * @return Address
     */
    public function getDefaultBillingAddress($erpAccountId)
    {
        $erpAccountInfo = $this->getErpAccountInfo($erpAccountId);
        $customer = $this->customerSessionFactory()->getCustomer();
        $defaultBillingAddressCode = $erpAccountInfo->getDefaultInvoiceAddressCode();
        $billingAddress = $erpAccountInfo->getAddress($defaultBillingAddressCode);
        $billingAddress = $billingAddress->toCustomerAddress($customer, $erpAccountId);

        return $billingAddress;
    }

    /**
     * Get the Product Price Precision from Configuration
     *
     * @return int
     */
    public function getProductPricePrecision()
    {
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION;
        $configPrecision = $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_PRICE_PRECISION, ScopeInterface::SCOPE_STORE);
        if ($configPrecision == "") {
            return $precision;
        }
        return $configPrecision;
    }

    /**
     * Gets if address lenght limit is enabled/disabled
     * @return bool
     */
    public function isAddressLimitEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CUSTOMER_ADDRESS_LIMITS_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /** Gets the Address Length Limit for given field
     * @param string $field
     * @return mixed
     */
    public function getAddressCharacterLimit($field)
    {
        $xmlPath = 'customer/address/limit_' . $field . '_length';
        return $this->getConfig($xmlPath);
    }

    /**
     * @return bool
     */
    public function isPriceDisplayDisabled()
    {
        return $this->isGuestLoggedInPriceDisplayDisabled() || $this->isGuestNotLoggedInPriceDisplayDisabled();
    }

    /**
     * @return bool
     */
    private function isGuestLoggedInPriceDisplayDisabled()
    {
        return $this->isDisableFunctionalityActive() && $this->isPriceDisplaySelected(self::TYPE_GUEST_LOGGED_IN);
    }

    /**
     * @return bool
     */
    private function isGuestNotLoggedInPriceDisplayDisabled()
    {
        return $this->isDisableFunctionalityActive() && $this->isPriceDisplaySelected(self::TYPE_GUEST_NOT_LOGGED_IN);
    }


    /**
     * @param $selection
     * @return array|mixed
     */
    private function getSerialisedSelection($selection)
    {
        $result = [];
        try {
            if ($selection) {
                $result = unserialize($selection);
            }
        } catch (\Exception $e) {
            $this->context->getLogger()->error($e->getMessage());
        }

        return $result;
    }

    /**
     * @param $type
     * @return bool
     */
    private function isPriceDisplaySelected($type)
    {
        if ($type === self::TYPE_GUEST_LOGGED_IN || $type === self::TYPE_GUEST_NOT_LOGGED_IN) {
            switch ($type) {
                case self::TYPE_GUEST_LOGGED_IN:
                    if (!$this->isCustomerLoggedIn() || !$this->isGuestAccount()) {
                        $result = false;
                        break;
                    }
                    $selection = $this->getLoggedInPriceDisplaySelection();
                    $result = in_array('prices', $this->getSerialisedSelection($selection));
                    break;
                case self::TYPE_GUEST_NOT_LOGGED_IN:
                    if ($this->isCustomerLoggedIn()) {
                        $result = false;
                        break;
                    }
                    $selection = $this->getNotLoggedInPriceDisplaySelection();
                    $result = in_array('prices', $this->getSerialisedSelection($selection));
                    break;
                default:
                    $result = false;
            }

            return $result;
        }
    }

    /**
     * @return bool
     */
    private function isGuestAccount()
    {
        return $this->getCustomer()->isGuest();
    }

    /**
     * @return bool
     */
    private function isCustomerLoggedIn()
    {
        $customerSession = $this->getCustomerSession()->create();

        return $customerSession->isLoggedIn();
    }

    /**
     * @return bool
     */
    private function isDisableFunctionalityActive()
    {
        return (bool)$this->scopeConfig->getValue(
            'customer/disabled_functionality/active',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    private function getNotLoggedInPriceDisplaySelection()
    {
        return (string)$this->scopeConfig->getValue(
            'customer/disabled_functionality/guests_logged_out',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    private function getLoggedInPriceDisplaySelection()
    {
        return (string) $this->scopeConfig->getValue(
            'customer/disabled_functionality/guests_logged_in',
            ScopeInterface::SCOPE_STORE
        );
    }
}
