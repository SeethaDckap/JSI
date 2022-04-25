<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper\Catalog\Product;


class Configuration extends \Magento\Catalog\Helper\Product\Configuration
{

    /**
     * Retrieves configuration options for grouped product
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return array
     */
    public function getGroupedOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        if ($typeId != \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Wrong product type to extract configurable options.'));
        }

        $options = array();
        /**
         * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
         */
        $typeInstance = $product->getTypeInstance(true);
        $associatedProducts = $typeInstance->getAssociatedProducts($product);

        if ($associatedProducts) {
            foreach ($associatedProducts as $associatedProduct) {
                $qty = $item->getOptionByCode('associated_product_' . $associatedProduct->getId());
                $option = array(
                    'label' => $associatedProduct->getName() . ' [' . $associatedProduct->getEccPackSize() . ']',
                    'value' => ($qty && $qty->getValue()) ? $qty->getValue() : 0
                );

                $options[] = $option;
            }
        }

        $options = array_merge($options, $this->getCustomOptions($item));
        $isUnConfigured = true;
        foreach ($options as &$option) {
            if ($option['value']) {
                $isUnConfigured = false;
                break;
            }
        }
        return $isUnConfigured ? array() : $options;
    }

}
