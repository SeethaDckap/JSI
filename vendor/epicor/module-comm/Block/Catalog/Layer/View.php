<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Layer;

/**
 * Catalog layered navigation view block
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class View extends \Magento\LayeredNavigation\Block\Navigation
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\FilterList $filterList,
        \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $layerResolver,
            $filterList,
            $visibilityFlag,
            $data
        );
    }


    /**
     * Get all layer filters
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array();
        if ($categoryFilter = $this->_getCategoryFilter()) {
            $filters[] = $categoryFilter;
        }

        $helper = $this->helper('epicor_comm');
        /* @var $helper Epicor_Comm_Helper_Data */
        $showPrices = $helper->isFunctionalityDisabledForCustomer('prices') ? false : true;

        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if (($code == 'price' && $showPrices) || $code != 'price') {
                $filters[] = $this->getChild($attribute->getAttributeCode() . '_filter');
            }
        }

        return $filters;
    }

}
