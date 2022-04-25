<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Cart;

use Magento\Store\Model\ScopeInterface;

class AdditionalConfig
{
    const REORDER_OPTION = 'sales/reorder/cart_merge_action';
    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    protected $listsFrontendProductHelper;
    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    protected $listFrontendHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealerHelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    protected $listsFrontendRestrictedHelper;

    protected $_dealerControllers = [
        'quotes',
        'orders'
    ];

    protected $customerconnectHelper;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    private $catalogProductResourceModelFactory;

    public function __construct(
        \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper,
        \Epicor\Lists\Helper\Frontend $listFrontendHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Helper\Data $dealerHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $catalogProductResourceModelFactory
    )
    {
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->listFrontendHelper = $listFrontendHelper;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->localeFormat = $localeFormat;
        $this->registry = $registry;
        $this->dealerHelper = $dealerHelper;
        $this->scopeConfig = $scopeConfig;
        $this->commHelper = $commHelper;
        $this->listsFrontendRestrictedHelper = $listsFrontendRestrictedHelper;
        $this->customerconnectHelper = $dealerHelper->getCustomerconnectHelper();
        $this->catalogProductResourceModelFactory = $catalogProductResourceModelFactory;
    }

    public function afterGetConfig(\Magento\Checkout\Block\Cart\Sidebar $subject, $return)
    {
        $currentProduct = $this->registry->registry('current_product');
        $displayConfigurablePriceDiff = '';
        if($currentProduct){
            //retrieve ecc_price_differential_display value
            $productConfigPriceDiff = $this->catalogProductResourceModelFactory->create()
                ->getAttributeRawValue($currentProduct->getId(), 'ecc_price_differential_display',0);

            //if value is 0 (no) don't display, if 1 (yes), do display, if 2 (use global value)
            switch ($productConfigPriceDiff) {
                case 0:
                    $displayConfigurablePriceDiff = 0;
                    break;
                 case 1:
                    $displayConfigurablePriceDiff = 1;
                    break;
                default:
                    $displayConfigurablePriceDiff = $this->scopeConfig
                        ->getValue('epicor_product_config/configurable_price_differential/configurable_price_differential_display');
                    break;
            }

        }

        $productHelper = $this->listsFrontendProductHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Product */
        $listHelper = $this->listFrontendHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend */
        $module = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        $dealerHelper = $this->dealerHelper;
        /* @var $helper Epicor_Dealerconnect_Helper_Data */
        $toggleAllowed = $dealerHelper->checkCustomerToggleAllowed();
        $return["isListsEnabled"] = $listHelper->listsEnabled() && $productHelper->hasFilterableLists() ? 1 : 0;
        $return['dealerPriceMode'] = $this->customerSession->getDealerCurrentMode() ?: NULL;
        $return['dealerPortal'] = $module === "dealerconnect" && (in_array($controller, $this->_dealerControllers)) ? 1 : 0;
        $return['priceFormat'] = $this->localeFormat->getPriceFormat();
        $return['rfqEditable'] = $this->registry->registry('rfqs_editable') ? 1 : 0;
        $return['dealerCanShowCusPrice'] = ($toggleAllowed !== "disabletoggle") ? $dealerHelper->checkCustomerCusPriceAllowed() : "disable";
        $return['dealerCanShowMargin'] = ($toggleAllowed !== "disabletoggle") ? $dealerHelper->checkCustomerMarginAllowed() : "disable";
        $return['eccNonErpProductsActive'] = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/enabled');
        $return['saveCartAsListOptions'] = $this->scopeConfig->getValue('epicor_lists/savecartaslist/savelistas');
        $return['enableCartToListAt'] = $this->listsFrontendRestrictedHelper->isCartAsListActive();
        $return['customerLoggedIn'] = $this->customerSession->isLoggedIn();
        $return['isHidePrices'] = $this->commHelper->getEccHidePrice() && in_array($this->commHelper->getEccHidePrice(), [1,3]);
        $return['showMiscCharge'] = $this->customerconnectHelper->showMiscCharges() ? 1 : 0;
        $return['displayConfigurablePriceDiff'] =  $displayConfigurablePriceDiff;
        $return['cartReorderOption'] = $this->scopeConfig->getValue(self::REORDER_OPTION, ScopeInterface::SCOPE_STORE);
        $return['isPriceDisplayDisabled'] = $this->commHelper->isPriceDisplayDisabled();

        return $return;
    }

}
