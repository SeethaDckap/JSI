<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\Quote\Item;

class RelatedProducts
{
    /**
     * List of related product types
     *
     * @var array
     */
    protected $_relatedProductTypes;

    /**
     * @param array $relatedProductTypes
     */
    public function __construct($relatedProductTypes = [])
    {
        $this->_relatedProductTypes = $relatedProductTypes;
    }

    /**
     * Retrieve Array of product ids which have special relation with products in Cart
     *
     * @param \Epicor\Customerconnect\Model\ArPayment\Quote\Item[] $quoteItems
     * @return int[]
     */
    public function getRelatedProductIds(array $quoteItems)
    {
        $productIds = [];
        /** @var $quoteItems \Epicor\Customerconnect\Model\ArPayment\Quote\Item[] */
        foreach ($quoteItems as $quoteItem) {
            $productTypeOpt = $quoteItem->getOptionByCode('product_type');
            if ($productTypeOpt instanceof \Epicor\Customerconnect\Model\ArPayment\Quote\Item\Option) {
                if (in_array(
                    $productTypeOpt->getValue(),
                    $this->_relatedProductTypes
                ) && $productTypeOpt->getProductId()
                ) {
                    $productIds[] = $productTypeOpt->getProductId();
                }
            }
        }
        return $productIds;
    }
}
