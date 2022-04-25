<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;


class DeleteUrlAtAttributesetDelete
{

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;
    
    /** @var UrlPersistInterface */
    protected $urlPersist;

    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        UrlPersistInterface $urlPersist
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->urlPersist = $urlPersist;
    }

    public function aroundDelete(
        \Magento\Eav\Model\AttributeSetRepository $subject,
        \Closure $proceed,
        \Magento\Eav\Api\Data\AttributeSetInterface $attributeSet
    ) {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addFieldToFilter('attribute_set_id',$attributeSet->getId());
        $productIds = $productCollection->getAllIds();
        $result = $proceed($attributeSet);       
        if (count($productIds)) {           
                $this->urlPersist->deleteByData([
                    UrlRewrite::ENTITY_ID => $productIds,
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                ]);
        }
         
        return $result;
    }
    
    
    
}