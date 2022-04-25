<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Checkout\CustomerData;

/**
 * Mini cart items 
 */
class CartPlugin
{
    /**
     * @var \Magento\Checkout\CustomerData\ItemPoolInterface
     */
    protected $itemPoolInterface;
    
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Url
     */
    protected $catalogUrl;
    
    /**
     * 
     * @param \Magento\Checkout\CustomerData\ItemPoolInterface $itemPoolInterface
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     */
    public function __construct(
        \Magento\Checkout\CustomerData\ItemPoolInterface $itemPoolInterface,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
    )
    {
        $this->itemPoolInterface = $itemPoolInterface;
        $this->cart = $cart;
        $this->catalogUrl = $catalogUrl;
    }
    
    /**
     * Updating mini cart items to show kit components
     * 
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $result['items'] = $this->updateRecentItems($subject);
        return $result;
    }
    
    /**
     * Including kit component in the mini cart
     * 
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @return array
     */
    protected function updateRecentItems($subject)
    {
        $items = [];
        $summaryCount = $this->cart->getSummaryQty() ?: 0;
        if (!$summaryCount) {
            return $items;
        }
        
        $allQuoteItems = ($this->cart->getCustomQuote()) ? $this->cart->getCustomQuote()->getAllVisibleItems() : $this->cart->getQuote()->getAllVisibleItems();

        foreach (array_reverse($allQuoteItems) as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            $product =  $item->getOptionByCode('product_type') !== null
                ? $item->getOptionByCode('product_type')->getProduct()
                : $item->getProduct();

            $products = $this->catalogUrl->getRewriteByProductStore([$product->getId() => $item->getStoreId()]);
            if (isset($products[$product->getId()])) {
                $urlDataObject = new \Magento\Framework\DataObject($products[$product->getId()]);
                $item->getProduct()->setUrlDataObject($urlDataObject);
            }
        
            $items[] = $this->itemPoolInterface->getItemData($item);
    }
        return $items;
    }
}