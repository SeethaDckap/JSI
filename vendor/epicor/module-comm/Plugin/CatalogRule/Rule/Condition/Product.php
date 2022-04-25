<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\CatalogRule\Rule\Condition;


use Epicor\Comm\Model\Catalog\ProductCategoryList;
use Magento\Framework\App\ObjectManager;

/**
 * Class State
 */
class Product
{
    /**
     * @var ProductCategoryList
     */
    private $productCategoryList;
    
    public function __construct(
        ProductCategoryList $categoryList = null
    ) {
        $this->productCategoryList = $categoryList ?: ObjectManager::getInstance()->get(ProductCategoryList::class);
    }
        
    public function aroundValidate(
        \Magento\CatalogRule\Model\Rule\Condition\Product $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $model
    ) {
        
        $attrCode = $subject->getAttribute();
        if ('category_ids' == $attrCode) {
            $productId = (int)$model->getEntityId();
            return $subject->validateAttribute($this->productCategoryList->getCategoryIds($productId));
        }
        return $proceed($model);
    }
}
