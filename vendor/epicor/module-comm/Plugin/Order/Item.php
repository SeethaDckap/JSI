<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Order;


class Item
{

    /**
     * @var \Magento\Eav\Model\Attribute\Data\Text
     */
    protected $ewaHelper;


    public function __construct(
        \Epicor\Comm\Helper\Configurator $ewaHelper
    ) {
        $this->ewaHelper = $ewaHelper;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundGetProductOptions(
        \Magento\Sales\Model\Order\Item $subject,
        \Closure $proceed
    ) {
        $options = $proceed();
        $product = $subject->getProduct();
        /* @var $product \Magento\Catalog\Model\Product|null */
        if ($product && $product->getCustomAttribute('ecc_configurator') && $product->getCustomAttribute('ecc_configurator')->getValue()) {
            if (isset($options['options'])) {
                $options['options'] = $this->ewaHelper->getEwaOptions($options['options']);
            }
        }
        return $options;
    }

    /**
     * Process value before validation
     *
     * @param bool|string|array $value
     * @return array list of lines represented by given value
     */
    protected function processValue($value)
    {
        if ($value === false) {
            // try to load original value and validate it
            $attribute = $this->getAttribute();
            $entity = $this->getEntity();
            $value = $entity->getDataUsingMethod($attribute->getAttributeCode());
        }
        if (!is_array($value)) {
            $value = explode("\n", $value);
        }
        return $value;
    }
    
}