<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout\Cart\Item\Renderer\Actions;

class Edit extends \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit
{
    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\GroupedFactory
     */
    protected $groupedProductProductTypeGroupedFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var  \Magento\Framework\Url\HelperInterface
     */
    protected $urlHelper;
    
    /**
     * @var  \Magento\Catalog\Helper\Product\Configuration
     */
    protected $productConfig;

    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\GroupedProduct\Model\Product\Type\GroupedFactory $groupedProductProductTypeGroupedFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
            array $data = [])
    {
        $this->groupedProductProductTypeGroupedFactory = $groupedProductProductTypeGroupedFactory;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->urlHelper = $urlHelper;
        $this->productConfig = $productConfig;
        
        parent::__construct($context, $data);
    }
    
    /**
     * Get item configure url
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        $options = $this->productConfig->getCustomOptions($this->getItem());
        $configurator = false;
        foreach ($options as $option) {
            if ($option['option_type'] == 'ewa_code') {
                $configurator = $option['value'];
                $productId = $this->getItem()->getProductId();
                $productLoad = $this->catalogProductFactory->create()->load($productId);
                $productType = ($productLoad->getEccProductType()) ? $productLoad->getEccProductType() : "";
                break;
            }
        }
        
        if ($configurator) {
            $currentStoreId = $this->_storeManager->getStore()->getStoreId();
            return "javascript: ewaProduct.edit({ewaCode: '$configurator',currentStoreId:'$currentStoreId', type: '$productType', productId: '$productId', itemId: '{$this->getItem()->getId()}'}, false);";
        } else {
            return parent::getConfigureUrl();
        }
    }
    
    public function isGroupProduct($product)
    {
        if ($product->getTypeId() == "simple") {
            $parentIds = $this->groupedProductProductTypeGroupedFactory->create()->getParentIdsByChild($product->getId());
            if (!empty($parentIds)) {
                $parentProduct = $this->catalogResourceModelProductCollectionFactory->create()->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id', array('in' => $parentIds))
                    ->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', 1);
                //$this->catalogProductVisibility->addVisibleInSiteFilterToCollection($parentProduct);
                $parentProduct->setVisibility($this->catalogProductVisibility->getVisibleInSiteIds()); 
                if ($parentProduct->getSize() > 0) {
                    return $parentProduct->getFirstItem();
                }
            }
        }
    }
    
    public function getProductUrlPath($id)
    {
        $product = $this->catalogProductFactory->create()->load($id);
        return $product->getProductUrl();
    }
    
    public function addUrlParams($url , $params=array())
    {
        return $this->urlHelper->addRequestParam($url , $params);
    }
    
}
