<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Pricing\Configurable;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use Magento\Framework\App\ObjectManager;
use Epicor\Comm\Model\Message\Request\MsqFactory;
use Magento\Framework\App\ProductMetadataInterface;

class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
    /**
     * @var LowestPriceOptionsProviderInterface
     */
    private $lowestPriceOptionsProvider;

    /**
     * @var ConfigurableOptionsProviderInterface
     */
    private $configurableOptionsProvider;

    /**
     * @var \Epicor\Comm\Model\Message\Request\MsqFactory
     */
    protected $commMessageRequestMsqFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 100.1.0
     */
    protected $productMetadata;

    /**
     * @var \Epicor\Comm\Model\Product
     */
    private $pricingSkuProduct = null;


    /**
     * FinalPriceBox constructor.
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param SalableResolverInterface $salableResolver
     * @param MinimalPriceCalculatorInterface $minimalPriceCalculator
     * @param ConfigurableOptionsProviderInterface $configurableOptionsProvider
     * @param MsqFactory $commMessageRequestMsqFactory
     * @param ProductMetadataInterface $productMetadata
     * @param LowestPriceOptionsProviderInterface|null $lowestPriceOptionsProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        SalableResolverInterface $salableResolver,
        MinimalPriceCalculatorInterface $minimalPriceCalculator,
        ConfigurableOptionsProviderInterface $configurableOptionsProvider,
        MsqFactory $commMessageRequestMsqFactory,
        ProductMetadataInterface $productMetadata,
        LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data,
            $salableResolver,
            $minimalPriceCalculator
        );
        $this->commMessageRequestMsqFactory = $commMessageRequestMsqFactory;
        $this->productMetadata = $productMetadata;
        $this->configurableOptionsProvider = $configurableOptionsProvider;
        $this->lowestPriceOptionsProvider = $lowestPriceOptionsProvider ?:
            ObjectManager::getInstance()->get(LowestPriceOptionsProviderInterface::class);

    }

    /**
     * Define if the special price should be shown
     *
     * @return bool
     */
    public function hasSpecialPrice()
    {
        $msq = $this->commMessageRequestMsqFactory->create();
        if ($msq->isActive() && $this->getSaleableItem()->getTypeId() == 'configurable') {
            $pricingSkuProd = $this->getPricingSkuProduct();
            if ($pricingSkuProd !== false) {
                $displayRegularPrice = $pricingSkuProd->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
                $displayFinalPrice = $pricingSkuProd->getPriceInfo()->getPrice('special_price')->getAmount()->getValue();
            } else {
                $displayRegularPrice = $this->getPriceType('regular_price')->getAmount()->getValue();
                $displayFinalPrice = $this->getPriceType('special_price')->getAmount()->getValue();
            }
            return ($displayFinalPrice && ($displayFinalPrice < $displayRegularPrice));
        }

        if ($this->productMetadata->getVersion() > '2.3.3') {
            $product = $this->getSaleableItem();
            foreach ($this->configurableOptionsProvider->getProducts($product) as $subProduct) {
                $regularPrice = $subProduct->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getValue();
                $finalPrice = $subProduct->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
                if ($finalPrice < $regularPrice) {
                    return true;
                }
            }
        } else {
            $product = $this->getSaleableItem();
            foreach ($this->lowestPriceOptionsProvider->getProducts($product) as $subProduct) {
                $regularPrice = $subProduct->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getValue();
                $finalPrice = $subProduct->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
                if ($finalPrice < $regularPrice) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * M2.3.3. going to cache final price
     *
     * @return bool|int|null
     */
    protected function getCacheLifetime()
    {
        return parent::hasCacheLifetime() ? parent::getCacheLifetime() : 0;
    }

    /**
     * Define final price to be shown
     *
     * @return PriceInterface
     */
    public function getFinalPriceModel()
    {
        $pricingSku = $this->getPricingSku();
        if (!$pricingSku) {
            $finalPriceModel = $this->getPriceType('final_price');
        } else {
            $finalPriceModel = $this->getPriceType('special_price');
            if (!$finalPriceModel->getValue()) {
                $finalPriceModel = $this->getPriceType('final_price');
            }
            $pricingSkuProd = $this->getPricingSkuProduct();
            if ($pricingSkuProd !== false) {
                $finalPriceModel = $pricingSkuProd->getPriceInfo()->getPrice('final_price');
            }
        }
        return $finalPriceModel;
    }

    /**
     * Can show both customer and base price
     *
     * @return bool
     */
    public function showBothPrices()
    {
        $pricingSku = $this->getPricingSku();
        if ((!$this->isProductList() && $this->hasSpecialPrice()) || ($pricingSku && $this->hasSpecialPrice())) {
            return true;
        }
        return false;
    }

    /**
     * get product pricing sku
     *
     * @return string
     */
    public function getPricingSku()
    {
        $product = $this->getSaleableItem();
        $pricingSku = $product->getEccPricingSku();
        return $pricingSku;
    }

    /**
     * Display Customer Price as Base Price?
     *
     * @return bool
     */
    public function getCustomerPriceUsed()
    {
        return $this->_scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/cusomterpriceused', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Pricing SKU product
     *
     * @return \Epicor\Comm\Model\Product|false|null
     */
    private function getPricingSkuProduct()
    {
        $pricingSku = $this->getPricingSku();
        if (is_null($this->pricingSkuProduct) === true) {
            $this->pricingSkuProduct = false;
            $msqCollection = $this->getMsqCollection();
            if (!empty($msqCollection)) {
                foreach ($msqCollection as $subprod) {
                    if ($subprod->getSku() == $pricingSku) {
                        $this->pricingSkuProduct = $subprod;
                        break;
                    }
                }
            }
        }
        return $this->pricingSkuProduct;
    }

    /**
     * Define Regular price to be shown
     * @return PriceInterface
     */
    public function getRegularPriceModel()
    {
        $pricingSkuProd = $this->getPricingSkuProduct();
        if ($pricingSkuProd !== false) {
            $regularPriceModel = $pricingSkuProd->getPriceInfo()->getPrice('regular_price');
        } else {
            $regularPriceModel = $this->getPriceType('regular_price');
        }
        return $regularPriceModel;
    }
}
