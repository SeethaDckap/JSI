<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\Rule\Condition\Product;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

/**
 * List of attributes used in query building.
 *
 */
class AttributeList
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    private $attributeCollection = null;

    /**
     * @var @array
     */
    private $fieldNameMapping = [
        'price'        => 'price.price',
        'category_ids' => 'category.category_id',
    ];

    /**
     * Constructor.
     *
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param StoreManagerInterface      $storeManager
     */
    public function __construct(
        AttributeCollectionFactory $attributeCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->storeManager               = $storeManager;
    }

    /**
     * Retrieve attribute collection pre-filtered with only attribute usable in rules.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getAttributeCollection()
    {
        if ($this->attributeCollection === null) {
            $this->attributeCollection = $this->attributeCollectionFactory->create();
            $fieldNames = array('sku','category_ids');
            $this->attributeCollection->addFieldToFilter('attribute_code', $fieldNames)
                ->addFieldToFilter('backend_type', ['neq' => 'datetime']);
        }
        return $this->attributeCollection;
    }
}
