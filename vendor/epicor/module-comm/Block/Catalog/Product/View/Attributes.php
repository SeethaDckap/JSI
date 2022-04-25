<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product\View;
use Magento\Framework\Phrase;


class Attributes extends \Magento\Catalog\Block\Product\View\Attributes
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $context->getStoreManager();
        parent::__construct(
            $context,
            $registry,
            $priceCurrency,
            $data
        );
    }


    public function getAdditionalData(array $excludeAttr = array())
    {
        $data = array();
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        $truncateTrailingZeros = $this->scopeConfig->isSetFlag('epicor_product_config/weights/truncate_trailing_zeros', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $weightDecimalPrecision = $this->scopeConfig->getValue('epicor_product_config/weights/weight_decimal_precision', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($attribute->getIsVisibleOnFront() && !in_array($attributeCode, $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);

                 if ($value instanceof Phrase) {
                    $value = (string)$value;
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                }
                if (is_string($value) && strlen($value)) {
                    if ($attributeCode == 'weight') {
                        if (!empty($weightDecimalPrecision)) {
                            $value = \Zend_Locale_Math::round($value, $weightDecimalPrecision);
                        }
                        if ($truncateTrailingZeros) {
                            $value = floatval($value);
                        }
                    }
                    $data[$attributeCode] = array(
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value,
                        'code' => $attributeCode
                    );
                }
            }
        }
        return $data;
    }

}
