<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Helper;


class LazyLoader extends \Epicor\Comm\Helper\Data
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Product
     */
    protected $_commProductHelper;

    protected $wishListRestrictBlock = array("customer.wishlist.item.price","customer.wishlist.item.inner");

    /**
     * LazyLoader constructor.
     * @param Context $context
     * @param Product $commProductHelper
     */
    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_storeManager = $context->getStoreManager();
        $this->_commProductHelper = $commProductHelper;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getCurrentUrl(){
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isLazyLoad($type="list"){
        return $this->_commProductHelper->isLazyLoad($type);
    }

    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLoaderImageUrl(){
        $loaderImagepath = $this->_scopeConfig->getValue('catalog/lazy_load/image_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($loaderImagepath) {
            $store = $this->_storeManager->getStore();
            return $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "lazyloader/$loaderImagepath";
        }
        return false;
    }

    /**
     * @return string
     */
    public function getLoaderUrl(){
        $param['allow_url'] = 0;
        return $this->_urlBuilder->getUrl("comm/lazyload/price", $param);
    }

    /**
     * @return array
     */
    public function getWishListRestrictBlock() {
        return $this->wishListRestrictBlock ? $this->wishListRestrictBlock : [];
    }

}