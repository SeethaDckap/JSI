<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Bundle\Catalog\Product;


/**
 * Bundle product view override
 * 
 * To cope with teir prices being fixed for fixed price bundles
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class View extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Helper\Data $taxHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->taxHelper = $taxHelper;
    }
    /**
     * Get tier prices (formatted)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getTierPrices($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $prices = $product->getFormatedTierPrice();

        $res = array();
        if (is_array($prices)) {
            if ($product->getPriceType() == 1) {
                foreach ($prices as $price) {
                    $price['price_qty'] = $price['price_qty'] * 1;

                    $_productPrice = $product->getPrice();
                    if ($_productPrice != $product->getFinalPrice()) {
                        $_productPrice = $product->getFinalPrice();
                    }
                    // Group price must be used for percent calculation if it is lower
                    $groupPrice = $product->getGroupPrice();
                    if ($_productPrice > $groupPrice && $groupPrice != 0) {
                        $_productPrice = $groupPrice;
                    }

                    $precision = $this->scopeConfig->getValue('epicor_common/tier_prices/precision', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $price['savePercent'] = round(100 - ((100 / $_productPrice) * $price['price']), $precision);

                    $price['formated_price'] = $this->storeManager->getStore()->formatPrice($this->storeManager->getStore()->convertPrice($this->taxHelper->getPrice($product, $price['website_price'])));
                    $price['formated_price_incl_tax'] = $this->storeManager->getStore()->formatPrice($this->storeManager->getStore()->convertPrice($this->taxHelper->getPrice($product, $price['website_price'], true)));
                    $res[] = $price;
                }
            } else {
                foreach ($prices as $price) {
                    $price['price_qty'] = $price['price_qty'] * 1;
                    $price['savePercent'] = ceil(100 - $price['price']);
                    $price['formated_price'] = $this->storeManager->getStore()->formatPrice($this->storeManager->getStore()->convertPrice($this->taxHelper->getPrice($product, $price['website_price'])));
                    $price['formated_price_incl_tax'] = $this->storeManager->getStore()->formatPrice($this->storeManager->getStore()->convertPrice($this->taxHelper->getPrice($product, $price['website_price'], true)));
                    $res[] = $price;
                }
            }
        }

        return $res;
    }

}
