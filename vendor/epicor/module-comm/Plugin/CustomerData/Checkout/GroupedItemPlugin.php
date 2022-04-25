<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\CustomerData\Checkout;

/**
 * Default item
 */
class GroupedItemPlugin 
{
    /**
     * @var  \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;
    
    /**
     * @var  \Magento\Framework\Url\Helper\Data
     */
    protected $item;
    
    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Url\Helper\Data $urlHelper
    ) {
        $this->urlHelper = $urlHelper;
    }
    
    /**
     * Retrieve URL to item Product
     *
     * @return string
     */
    protected function getProductUrl()
    { 
        if ($this->item->getRedirectUrl()) { 
            return $this->item->getRedirectUrl();
        }

        $product = $this->item->getProduct();
        $option = $this->item->getOptionByCode('product_type');
        if ($option) {
            $product = $option->getProduct();
        }

        return $product->getUrlModel()->getUrl($product);
    }
    
    /**
     * Get item configure url
     *
     * @return string
     */
    protected function getConfigureUrl()
    {
        
        $params = [
            'itemid' => $this->item->getId(),
            'packsize' => $this->item->getProduct()->getId(),
            'qty' => $this->item->getQty(),
            'recon'  => 'y',
        ];
        
        $url = $this->getProductUrl();
        return $this->urlHelper->addRequestParam($url, $params);
    }
    
    /**
     * ECC mods to get item data
     * 
     * @param \Magento\GroupedProduct\CustomerData\GroupedItem $subject
     * @param array $result
     * 
     * @return array
     */
    public function beforeGetItemData($subject, $item)
    {
        $this->item = $item;
    }
    
    /**
     * ECC mods to get item data
     * 
     * @param \Magento\GroupedProduct\CustomerData\GroupedItem $subject
     * @param array $result
     * 
     * @return array
     */
    public function afterGetItemData($subject, $result)
    {
        $result['is_visible_in_site_visibility'] = 1;
        $result['configure_url'] = $this->getConfigureUrl();
        return $result;
    }

}
