<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Catalog\Product;

/**
 * Configurator Block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Configurator
    extends \Magento\Framework\View\Element\Template
{

    /**
     * Route path key to make redirect url.
     */
    private const ROUTE_PATH = 'route_path';

    /**
     * @var \Epicor\Comm\Helper\Locations 
     */
    protected $locationsHelper;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $canShow;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    private $layerResolver;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $catalogCategoryFactory;

    /**
     * Configurator constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context                $context
     * @param \Epicor\Comm\Helper\Locations                                   $locationsHelper
     * @param \Magento\Framework\App\Request\Http                             $request
     * @param \Magento\Framework\Registry                                     $registry
     * @param \Magento\Catalog\Model\Layer\Resolver                           $layerResolver
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $catalogCategoryFactory
     * @param array                                                           $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Comm\Helper\Locations $locationsHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $catalogCategoryFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->locationsHelper = $locationsHelper;
        $this->_request = $request;
        $this->scopeConfig = $context->getScopeConfig();
        $this->layerResolver=$layerResolver;
        $this->catalogCategoryFactory=$catalogCategoryFactory;
        parent::__construct(
            $context,
            $data
        );
    }
    
    /**
     * Gets the current product context
     * 
     * @return \Epicor\Comm\Model\Product $_product
     */
    public function getCurrentProduct() {
        
        return $this->registry->registry('current_product');
    }

    /**
     * Generates the json string required for configurator
     * 
     * @return string
     */
    public function getOnclickValue()
    {
        /* @var $_product \Epicor\Comm\Model\Product */
        $_product = $this->getCurrentProduct();
        $category = $this->getCurrentCategory();

        $data = [
            'sku' => $_product->getSku(),
            'currentStoreId' => $this->_storeManager->getStore()->getStoreId(),
            'productId' => $_product->getId(),
            'type' => $_product->getEccProductType(),
            'productCategory' => $category->getEccErpCode()?:null
        ];

        if ($this->locationsHelper->isLocationsEnabled()) {
            $data['location'] = $_product->getRequiredLocation();
        }

        
        return 'javascript:ewaProduct.submit(' . str_replace('"', "'", json_encode($data)) . ', false);';
    }

    /**
     * Returns whether the configurator can be shown
     * 
     * @return type
     */
    public function canShow()
    {
        $product = $this->getCurrentProduct();
        $productLocations = array_keys($product->getLocations());
        $stockVisibility = $this->scopeConfig->getValue('epicor_comm_locations/global/stockvisibility', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $allSourceLocations = ($stockVisibility == 'all_source_locations') ? true : false;
        $singleLocation = (count($product->getCustomerLocations()) == 1) ? true : false;
        $show = $this->registry->registry('epicor_ecc_can_show_configurator');

        if (is_null($this->registry->registry('epicor_ecc_can_show_configurator'))) {
            $erpAccount = $this->locationsHelper->getErpAccountInfo();
            /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */

            $licenseTypes = [];

            if ($erpAccount && $erpAccount->isTypeB2b()) {
                $licenseTypes[] = 'Customer_Configurator';
            } else {
                $licenseTypes[] = 'Consumer_Configurator';
            }

            $show = $this->locationsHelper->isLicensedFor($licenseTypes);
            $this->registry->register('epicor_ecc_can_show_configurator', $show, true);
        }
        
        $filterButton = true;
        if ($this->locationsHelper->isLocationsEnabled()) {
          $filterButton = false;  
        }
        
        if($allSourceLocations || $singleLocation || $filterButton){
            $show = true;
        }else{
            $show = false;
        }
        
        
        return $show;
    }
    
    
    public  function showPageConditions() 
    {
        $condition = false;
        if($this->_request->getFullActionName() =="catalog_product_view") {
          $condition = true; 
        }
        return $condition;
    }

    /**
     * Get Current Category.
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        return $this->layerResolver->get()->getCurrentCategory();
    }

    /**
     * Returns the Parameters from request
     * @return array
     */
    public function getParams()
    {
        $params = [];
        foreach ($this->_request->getParams() as $name => $value) {
            if (!empty($value) && mb_detect_encoding($value, 'UTF-8', true) === false) {
                $value = utf8_encode($value);
            }
            $params[$name] = $value;
        }
        return $params;
    }

    /**
     * Returns url for redirect.
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_urlBuilder->getUrl($this->getData(self::ROUTE_PATH));
    }

}
