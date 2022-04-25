<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Catalog;


/**
 * Catalog Navigation block override
 * 
 * Override to prevent category nav display if the customer doesnt have access
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Navigation extends \Magento\Catalog\Block\Navigation
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    protected $listsFrontendProductHelper;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $catalogCategoryFactory;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        array $data = []
    ) {
        $this->storeManager = $context->getStoreManager();
        $this->customerSession = $customerSession;
        $this->scopeConfig = $context->getScopeConfig();
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->commonHelper = $commonHelper;
        parent::__construct(
            $context,
            $categoryFactory,
            $productCollectionFactory,
            $layerResolver,
            $httpContext,
            $catalogCategory,
            $registry,
            $flatState,
            $data
        );
    }


    protected function _construct()
    {

        parent::_construct();

        $tags = $this->getData('cache_tags');

        $tags[] = 'CATALOG_NAVIGATION_HTML_CACHE';

        $this->addData(
            array(
                'cache_tags' => $tags,
            )
        );
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $shortCacheId = array(
            'CATALOG_NAVIGATION',
            $this->storeManager->getStore()->getId(),
            //M1 > M2 Translation Begin (Rule p2-5.4)
            /*Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),*/
            $this->_design->getDesignTheme()->getCode(),
            $this->_design->getDesignTheme()->getThemePath(),
            //M1 > M2 Translation End
            $this->customerSession->getCustomerGroupId(),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            'name' => $this->getNameInLayout(),
            $this->getCurrenCategoryKey()
        );


        $helper = $this->commonHelper;
        /* @var $helper Epicor_Common_Helper_Data */


        $extraKeys = $helper->getCategoryCacheKeys();
        $shortCacheId = array_merge($shortCacheId, $extraKeys);
        $cacheId = $shortCacheId;

        $shortCacheId = array_values($shortCacheId);
        $shortCacheId = implode('|', $shortCacheId);
        $shortCacheId = md5($shortCacheId);

        $cacheId['category_path'] = $this->getCurrenCategoryKey();
        $cacheId['short_cache_id'] = $shortCacheId;

        return $cacheId;
    }

    protected function _toHtml()
    {
        $html = '';

        if ($this->hasAccess()) {
            $html = parent::_toHtml();
        }

        return $html;
    }

    private function hasAccess()
    {
        $accessHelper = $this->commonAccessHelper;
        /* @var $helper Epicor_Common_Helper_Access */

        return $accessHelper->canAccessUrl('catalog/category/view');
    }

    /**
     * Render category to html
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param int Nesting level number
     * @param boolean Whether ot not this item is last, affects list item class
     * @param boolean Whether ot not this item is first, affects list item class
     * @param boolean Whether ot not this item is outermost, affects list item class
     * @param string Extra class of outermost list items
     * @param string If specified wraps children list in div with this class
     * @param boolean Whether ot not to add on* attributes to list item
     * @return string
     */
    protected function _renderCategoryMenuItemHtml($category, $level = 0, $isLast = false, $isFirst = false, $isOutermost = false, $outermostItemClass = '', $childrenWrapClass = '', $noEventAttributes = false)
    {
        if (!$category->getIsActive()) {
            return '';
        }
        $html = array();

        // get all children
        //M1 > M2 Translation Begin (Rule p2-7)
        //if (Mage::helper('catalog/category_flat')->isEnabled()) {
        if ($this->scopeConfig->getValue('catalog/frontend/flat_catalog_category')) {
            //M1 > M2 Translation End
            $children = (array) $category->getChildrenNodes();
            $childrenCount = count($children);
        } else {
            $children = $category->getChildren();
            $childrenCount = $children->count();
        }
        $hasChildren = ($children && $childrenCount);

        // select active children
        $activeChildren = array();


        $listHelper = $this->listsFrontendProductHelper;
        /* @var $listHelper Epicor_Lists_Helper_Frontend_Product */
        $autoHideEnabled = $listHelper->getAutohideCategories();

        foreach ($children as $child) {
            if ($child->getIsActive()) {
                if ($autoHideEnabled) {
                    $childCategory = $this->catalogCategoryFactory->create()->load($child->getId());
                    /* @var $childCategory Mage_Catalog_Model_Category */

                    $productCollection = $childCategory->getProductCollection();
                    $productCollection->addAttributeToFilter('visibility', array('in' => array(2, 4)));
                    $productCollection = $this->commonHelper->performLocationProductFiltering($productCollection);
                    $productCollection = $this->commonHelper->performContractProductFiltering($productCollection);
                    if ($productCollection->getSize() > 0) {
                        $activeChildren[] = $child;
                    }
                } else {
                    $activeChildren[] = $child;
                }
            }
        }

        $activeChildrenCount = count($activeChildren);
        $hasActiveChildren = ($activeChildrenCount > 0);

        // prepare list item html classes
        $classes = array();
        $classes[] = 'level' . $level;
        $classes[] = 'nav-' . $this->_getItemPosition($level);
        if ($this->isCategoryActive($category)) {
            $classes[] = 'active';
        }
        $linkClass = '';
        if ($isOutermost && $outermostItemClass) {
            $classes[] = $outermostItemClass;
            $linkClass = ' class="' . $outermostItemClass . '"';
        }
        if ($isFirst) {
            $classes[] = 'first';
        }
        if ($isLast) {
            $classes[] = 'last';
        }
        if ($hasActiveChildren) {
            $classes[] = 'parent';
        }

        // prepare list item attributes
        $attributes = array();
        if (count($classes) > 0) {
            $attributes['class'] = implode(' ', $classes);
        }
        if ($hasActiveChildren && !$noEventAttributes) {
            $attributes['onmouseover'] = 'toggleMenu(this,1)';
            $attributes['onmouseout'] = 'toggleMenu(this,0)';
        }

        // assemble list item with attributes
        $htmlLi = '<li';
        foreach ($attributes as $attrName => $attrValue) {
            $htmlLi .= ' ' . $attrName . '="' . str_replace('"', '\"', $attrValue) . '"';
        }
        $htmlLi .= '>';
        $html[] = $htmlLi;

        $html[] = '<a href="' . $this->getCategoryUrl($category) . '"' . $linkClass . '>';
        $html[] = '<span>' . $this->escapeHtml($category->getName()) . '</span>';
        $html[] = '</a>';

        // render children
        $htmlChildren = '';
        $j = 0;
        foreach ($activeChildren as $child) {
            $htmlChildren .= $this->_renderCategoryMenuItemHtml(
                $child, ($level + 1), ($j == $activeChildrenCount - 1), ($j == 0), false, $outermostItemClass, $childrenWrapClass, $noEventAttributes
            );
            $j++;
        }
        if (!empty($htmlChildren)) {
            if ($childrenWrapClass) {
                $html[] = '<div class="' . $childrenWrapClass . '">';
            }
            $html[] = '<ul class="level' . $level . '">';
            $html[] = $htmlChildren;
            $html[] = '</ul>';
            if ($childrenWrapClass) {
                $html[] = '</div>';
            }
        }

        $html[] = '</li>';

        $html = implode("\n", $html);
        return $html;
    }

    /**
     * Render categories menu in HTML
     *
     * @param int Level number for list item class to start from
     * @param string Extra class of outermost list items
     * @param string If specified wraps children list in div with this class
     * @return string
     */
    public function renderCategoriesMenuHtml($level = 0, $outermostItemClass = '', $childrenWrapClass = '')
    {
        $activeCategories = array();


        $autoHideHelper = $this->commonHelper;
        /* @var $autoHideHelper Epicor_Common_Helper_Data */
        $autoHideEnabled = $autoHideHelper->getAutohideCategories();

        foreach ($this->getStoreCategories() as $child) {
            if ($child->getIsActive()) {
                if ($autoHideEnabled) {
                    $category = $this->catalogCategoryFactory->create()->load($child->getId());
                    /* @var $category Mage_Catalog_Model_Category */
                    $productCollection = $category->getProductCollection();
                    $productCollection->addAttributeToFilter('visibility', array('in' => array(2, 4)));
                    $productCollection = $this->commonHelper->performLocationProductFiltering($productCollection);
                    $productCollection = $this->commonHelper->performContractProductFiltering($productCollection);
                    if ($productCollection->getSize() > 0) {
                        $activeCategories[] = $child;
                    }
                } else {
                    $activeCategories[] = $child;
                }
            }
        }

        $activeCategoriesCount = count($activeCategories);
        $hasActiveCategoriesCount = ($activeCategoriesCount > 0);

        if (!$hasActiveCategoriesCount) {
            return '';
        }

        $html = '';
        $j = 0;

        foreach ($activeCategories as $category) {
            $html .= $this->_renderCategoryMenuItemHtml(
                $category, $level, ($j == $activeCategoriesCount - 1), ($j == 0), true, $outermostItemClass, $childrenWrapClass, true
            );
            $j++;
        }

        return $html;
    }

}
