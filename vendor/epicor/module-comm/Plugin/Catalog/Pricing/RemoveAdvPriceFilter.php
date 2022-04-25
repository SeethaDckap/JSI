<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Catalog\Pricing;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;


class RemoveAdvPriceFilter
{
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Product factory
     *
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * Attribute collection factory
     *
     * @var AttributeCollectionFactory
     */
    protected $_attributeCollectionFactory;

    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        AttributeCollectionFactory $attributeCollectionFactory


    )
    {
        $this->commHelper = $commHelper;
        $this->_storeManager = $storeManager;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_productFactory = $productFactory;

    }

    /**
     * Retrieve array of attributes used in advanced search
     *
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function beforeGetAttributes(\Magento\CatalogSearch\Model\Advanced $subject)
    {
        $attributes = $subject->getData('attributes');
        $hidePrice = $this->commHelper->getEccHidePrice();
        if ($attributes === null) {
            $product = $this->_productFactory->create();
            $attributes = $this->_attributeCollectionFactory
                ->create()
                ->addHasOptionsFilter()
                ->addDisplayInAdvancedSearchFilter();
            if ($hidePrice && $hidePrice != 2) {
                $attributes->removePriceFilter();
            }
            $attributes->addStoreLabel($this->_storeManager->getStore()->getId())
                ->setOrder('main_table.attribute_id', 'asc')
                ->load();
            foreach ($attributes as $attribute) {
                $attribute->setEntity($product->getResource());
            }
            $subject->setData('attributes', $attributes);
        }
    }

}
