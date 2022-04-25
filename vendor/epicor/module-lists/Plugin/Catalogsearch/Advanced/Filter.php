<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Plugin\Catalogsearch\Advanced;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;


class Filter
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
    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    private $listsFrontendProductHelper;
    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    private $listsFrontendContractHelper;


    /*
    *\Magento\Framework\App\Config\ScopeConfigInterface
    */
    private $_scopeConfig;

    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper


    )
    {
        $this->_storeManager = $storeManager;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_productFactory = $productFactory;
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->_scopeConfig = $commHelper->getScopeConfig();
    }

    /**
     * add products in active lists to filter for advanced search
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterAddFilters($subject, $result){
        //if lists turned off, don't process
        if(!$this->_scopeConfig->isSetFlag('epicor_lists/global/enabled')){
            return $result;
        }
        $collection = $subject->getProductCollection();
        $helper = $this->listsFrontendProductHelper;
        /** @var \Epicor\Lists\Helper\Frontend\Product */
        $contractHelper = $this->listsFrontendContractHelper;
        /** @var \Epicor\Lists\Helper\Frontend\Contract */


        //if lists are filterable or must filter by contract, limit search to products available in list/contract
        if ($helper->hasFilterableLists() || $contractHelper->mustFilterByContract()) {

            $validProductIds = $helper->getActiveListsProductIds();
            $collection->getSelect()->where(
                '(e.entity_id IN(' . $validProductIds . '))'
            );
        }
        return $result;
    }

}