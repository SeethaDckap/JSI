<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Quote;


use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;

class Item extends \Magento\Quote\Model\Quote\Item
{

    protected $_optionsByType = null;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $catalogProductConfigurationHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\Status\ListFactory $statusListFactory,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory,
        \Magento\Quote\Model\Quote\Item\Compare $quoteItemCompare,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Helper\Product\Configuration $catalogProductConfigurationHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->catalogProductConfigurationHelper = $catalogProductConfigurationHelper;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $productRepository,
            $priceCurrency,
            $statusListFactory,
            $localeFormat,
            $itemOptionFactory,
            $quoteItemCompare,
            $stockRegistry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function getOptionByType($type)
    {
        $options = $this->getOptionsByType();

        return (isset($options[$type])) ? $options[$type] : null;
    }

    public function getOptionsByType()
    {
        $helper = $this->catalogProductConfigurationHelper;
        $options = $helper->getCustomOptions($this);

        foreach ($options as $option) {
            $this->_optionsByType[$option['option_type']] = $option['value'];
        }

        return $this->_optionsByType;
    }

}
