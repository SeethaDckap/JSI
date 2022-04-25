<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product;


    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

/**
 * Description of View
 *
 * @author Paul.Ketelle
 */
class View extends \Magento\Catalog\Block\Product\View
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     *
     * @var \Epicor\Comm\Helper\Product 
     */
    protected $_commProductHelper;
    
    /**
     * 
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Epicor\Comm\Helper\Product $commProductHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Product $commProductHelper,
        array $data = []
    )
    {
        $this->request = $request;
        $this->_commProductHelper = $commProductHelper;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    public function addToParentGroup($group)
    {
        $this->_layout->addToParentGroup('product.moreinfofile', $group);
    }

    public function getProduct()
    {
        $product = parent::getProduct();
        if ($this->request->getParam('qty', false)) {
            $product->setPreconfiguredValues(
                $product->getPreconfiguredValues()
                    ->setQty(
                        $this->request->getParam('qty')
                    )
            );
        }
        return $product;
    }

    public function getSkuEditUrl($entityId)
    {
        return $this->getUrl('customerconnect/skus/edit', array('id' => $entityId));
    }

    public function getSkuAddUrl($productId)
    {
        return $this->getUrl('customerconnect/skus/create', array('id' => $productId));
    }

    /**
     * Return price block
     *
     * @param string $productTypeId
     * @return mixed
     */
    public function _getPriceBlock($productTypeId)
    {
        return parent::_getPriceBlock($productTypeId);
    }
    
    /**
     * is lazy load
     * 
     * @return boolean
     */
    public function isLazyLoad(){
        $product = parent::getProduct();
        if ($product && $product->getConfigureMode()) {
            return false;
        }
        
        return $this->_commProductHelper->isLazyLoad("view");
    }
    
    public function getLoaderImageUrl(){
       $loaderImagepath = $this->_scopeConfig->getValue('catalog/lazy_load/image_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);      
       if($loaderImagepath) {
           $store = $this->_storeManager->getStore();    
           return $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "lazyloader/$loaderImagepath";
       }
       return $this->getViewFileUrl("images/loader-2.gif");
    }          
       
}
