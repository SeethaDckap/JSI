<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Catalog\Product;


/**
 * Product price block override
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 * 
 */
class Price extends \Magento\Catalog\Block\Product\Price
{

    /**
     * @var \Magento\Bundle\Model\Product\PriceFactory
     */
    protected $bundleProductPriceFactory;

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

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogHelper;
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Bundle\Model\Product\PriceFactory $bundleProductPriceFactory,

        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Framework\App\ProductMetadata $productMetadata,
        \Magento\Catalog\Helper\Data $catalogHelper,
        array $data = []
    ) {
        $this->bundleProductPriceFactory = $bundleProductPriceFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $context->getStoreManager();
        $this->taxHelper = $taxHelper;
        $this->productMetadata=$productMetadata;
        $this->catalogHelper = $catalogHelper;
        parent::__construct(
            $context,
            $jsonEncoder,
            $catalogData,
            $registry,
            $string,
            $mathRandom,
            $cartHelper,
            $data
        );
    }


    /**
     * Get tier prices (formatted)
     * 
     * Only difference to parent is the 'savePercent' can now be rounded to precision
     * rather than just rounding it up
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getTierPrices($product = null, $parent = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $prices = $product->getFormatedTierPrice();
        //M1 > M2 Translation Begin (Rule P2-5.10)
        //$version = Mage::getVersionInfo();
        $version = $this->productMetadata->getVersion();
        ////M1 > M2 Translation End

        if ($version['minor'] >= 9) {
            // if our parent is a bundle, then we need to further adjust our tier prices
            if (isset($parent) && $parent->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                /* @var $bundlePriceModel Mage_Bundle_Model_Product_Price */
                $bundlePriceModel = $this->bundleProductPriceFactory->create();
            }
        }

        $res = array();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $price['price_qty'] = $price['price_qty'] * 1;

                $productPrice = $product->getPrice();
                if ($product->getPrice() != $product->getFinalPrice()) {
                    $productPrice = $product->getFinalPrice();
                }

                // Group price must be used for percent calculation if it is lower
                $groupPrice = $product->getGroupPrice();
                if ($productPrice > $groupPrice) {
                    $productPrice = $groupPrice;
                }

                if ($price['price'] < $productPrice) {
                    // use the original prices to determine the percent savings
                    //$price['savePercent'] = ceil(100 - ((100 / $productPrice) * $price['price']));
                    $precision = $this->scopeConfig->getValue('epicor_common/tier_prices/precision', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $price['savePercent'] = round(100 - ((100 / $productPrice) * $price['price']), $precision);

                    // if applicable, adjust the tier prices
                    if (isset($bundlePriceModel)) {
                        $price['price'] = $bundlePriceModel->getLowestPrice($parent, $price['price']);
                        $price['website_price'] = $bundlePriceModel->getLowestPrice($parent, $price['website_price']);
                    }

                    $tierPrice = $this->storeManager->getStore()->convertPrice(
                        $this->taxHelper->getPrice($product, $price['website_price'])
                    );
                    $price['formated_price'] = $this->storeManager->getStore()->formatPrice($tierPrice);
                    $price['formated_price_incl_tax'] = $this->storeManager->getStore()->formatPrice(
                        $this->storeManager->getStore()->convertPrice(
                            $this->taxHelper->getPrice($product, $price['website_price'], true)
                        )
                    );

                    if ($this->catalogHelper->canApplyMsrp($product)) {
                        $oldPrice = $product->getFinalPrice();
                        $product->setPriceCalculation(false);
                        $product->setPrice($tierPrice);
                        $product->setFinalPrice($tierPrice);

                        $this->getLayout()->getBlock('product.info')->getPriceHtml($product);
                        $product->setPriceCalculation(true);

                        $price['real_price_html'] = $product->getRealPriceHtml();
                        $product->setFinalPrice($oldPrice);
                    }

                    $res[] = $price;
                }
            }
        }

        return $res;
    }

    public function callParentToHtml()
    {
        return parent::callParentToHtml();
    }

}
