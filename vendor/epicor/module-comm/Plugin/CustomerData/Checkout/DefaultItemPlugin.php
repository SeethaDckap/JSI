<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\CustomerData\Checkout;

/**
 * Default item
 */
class DefaultItemPlugin 
{
    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    protected $configurationPool;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var  \Magento\Framework\Url\Helper\Data
     */
    protected $item;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;    
    
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $helper;
    
    /**
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Helper\Data $helper
    ) {
        $this->storeManager = $storeManager;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->configurationPool = $configurationPool;
        $this->helper = $helper;
    }
    
    /**
     * ECC mods to get item data
     * 
     * @param \Magento\Checkout\CustomerData\DefaultItem $subject
     * @param array $result
     * 
     * @return array
     */
    public function beforeGetItemData($subject, $item)
    {
        $this->item = $item;
    }

    /**
     * Get item configure url
     *
     * @return string
     */
    protected function getConfigureUrl()
    {
        $options = $this->configurationPool->getByProductType($this->item->getProductType())->getOptions($this->item);
        $configurator = false;
        $url = false;
        foreach ($options as $option) {
            if (isset($option['option_type']) && $option['option_type'] == 'ewa_code') {
                $configurator = $option['value'];
                $productId = $this->item->getProductId();
                $productLoad = $this->catalogProductFactory->create()->load($productId);
                $productType = ($productLoad->getEccProductType()) ? $productLoad->getEccProductType() : "";
                break;
            }
        }

        if ($configurator) {
            $currentStoreId = $this->storeManager->getStore()->getStoreId();
            $url = "javascript: ewaProduct.edit({ewaCode: '$configurator',currentStoreId:'$currentStoreId', type: '$productType', productId: '$productId', itemId: '{$this->item->getId()}'}, false);";
        }
        
        return $url;
    }
    
    /**
     * ECC mods to get item data
     * 
     * @param \Magento\Checkout\CustomerData\DefaultItem $subject
     * @param array $result
     * 
     * @return array
     */
    public function afterGetItemData($subject, $result)
    {
        $url = $this->getConfigureUrl();
        if ($url) {
            $result['configure_url'] = $url;
        }
        $_product = $this->item->getProduct();
        $decimalPlaces = $this->helper->getDecimalPlaces($_product);
        $result['decimal_places'] = '';
        if ($decimalPlaces !== '') {
            $result['decimal_places'] = $decimalPlaces;
        }
        return $result;
    }
    
}
