<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Observer;

class ProductClearCatalogNavigationCache extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Common\Helper\AccessFactory $commonAccessHelper,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\DataFactory $commonHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Lists\Helper\Frontend\ProductFactory $listsFrontendProductHelper,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Framework\HTTP\Header $header,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\CacheInterface $cache
    )
    {
        $this->cache = $cache;
        parent::__construct($request, $commonAccessHelper, $customerSession, $scopeConfig, $frameworkHelperDataHelper, $eventManager, $generic, $backendJsHelper, $commonAccessGroupCustomerFactory, $registry, $commonHelper, $backendAuthSession, $commCustomerErpaccountAddressFactory, $catalogProductFactory, $listsFrontendProductHelper, $catalogCategoryFactory, $header, $url, $response);
    }

    /**
     * Clearing category navigation cache after a product save
     *
     * Catalog category cache is only cleared if Auto-hide is enabled and:
     *
     * - It is a new product and it is visible
     * - Product visibility has changed
     * - Product status has changed (enabled / disabled)
     * - Product is deleted
     * - Catgeories has changed
     *
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $autoHideEnabled = $this->scopeConfig->getValue('epicor_common/catalog_navigation/auto_hide', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($autoHideEnabled) {

            $event = $observer->getEvent();
            /* @var $event Varien_Event */
            $product = $event->getProduct();
            /* @var $product Epicor_Comm_Model_Product */

            $origData = $product->getOrigData();
            $data = $product->getData();

            $clearCache = false;

            $origCategoryIds = $this->catalogProductFactory->create()->load($product->getId())->getCategoryIds();
            $newCategories = $product->getCategoryIds();

            $diff = array_merge(
                array_diff_assoc($origCategoryIds, $newCategories), array_diff_assoc($newCategories, $origCategoryIds)
            );
            $visibility = isset($data['visibility']) ? $data['visibility'] : '';
            $origDataVisibility = isset($origData['visibility']) ? $origData['visibility'] : '';
            $status = isset($data['status']) ? $data['status'] : '';
            $origDataStatus = isset($origData['status']) ? $origData['status'] : '';
            if ($product->isObjectNew() || !empty($diff) || $visibility != $origDataVisibility || $status != $origDataStatus) {
                $clearCache = true;
            }

            if ($clearCache) {
                //M1 > M2 Translation Begin (Rule p2-6.7)
                //$cache = Mage::app()->getCacheInstance();
                $cache = $this->cache;
                //M1 > M2 Translation End
                /* @var $cache Mage_Core_Model_Cache */
                $cache->clean(array('CATALOG_NAVIGATION_HTML_CACHE'));
            }
        }
    }

}