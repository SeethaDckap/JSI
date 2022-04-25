<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product\Listing;


/**
 * Locations
 *
 * Displays Locations on the product list page
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Locations extends \Epicor\Comm\Block\Catalog\Product\Locations
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    private $layerResolver;

    /**
     * Locations constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Msrp\Helper\Data                        $catalogHelper
     * @param \Magento\Catalog\Model\Layer\Resolver            $layerResolver
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Msrp\Helper\Data $catalogHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->catalogHelper = $catalogHelper;
        $this->layerResolver=$layerResolver;
        parent::__construct(
            $context,
            $registry,
            $catalogHelper,
            $data
        );
    }

    /**
     * Gets the list mode
     *
     * @return string
     */
    public function getListMode()
    {
        return $this->registry->registry('list_mode');
    }

    public function getProductUrl()
    {
        return $this->getParentBlock()->getProductUrl();
    }

    public function resetProduct()
    {
        $this->getProduct()->restoreOrigData();
    }

    /**
     * view mode just use for substitute item
     * because of cart cross-sell and substitute
     * cross location model binding with same ID
     *
     * value is substitute|null
     *
     * @return string
     */
    public function getViewMode()
    {
        return $this->getData("view_type") ? "-" . $this->getData("view_type") : "";
    }

    /**
     * Check for homepage
     *
     * @return bool
     */
    public function isHomepage(): bool
    {
        return $this->_request->getFullActionName() === 'cms_index_index';
    }

    /**
     * get Category Erp code
     *
     * @return string
     */
    public function getEccErpCategoryCode()
    {
        $category = $this->getCurrentCategory();
        if ($category && $category->getEccErpCode()) {
            return $category->getEccErpCode();
        }

        return "";
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

}
