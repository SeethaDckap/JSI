<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\SyncDecoder\Model;

use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data as QuoteApi;

class CartItemProcessor implements CartItemProcessorInterface
{
    protected $objectFactory;

    protected $productOptionExtensionFactory;

    protected $productOptionFactory;

    protected $additionOptionFactory;

    public function __construct(
        \Magento\Framework\DataObject\Factory $objectFactory,
        QuoteApi\ProductOptionExtensionFactory $productOptionExtensionFactory,
        QuoteApi\ProductOptionInterfaceFactory $productOptionFactory,
        \Silk\SyncDecoder\Model\AdditionalOptionFactory $additionOptionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->productOptionExtensionFactory = $productOptionExtensionFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->additionOptionFactory = $additionOptionFactory;
    }

    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        if ($cartItem->getProductOption() && $cartItem->getProductOption()->getExtensionAttributes()) {
            $additionalOptions = $cartItem->getProductOption()->getExtensionAttributes()->getAdditionalOptions();
            $locationId = $cartItem->getProductOption()->getExtensionAttributes()->getLocationId();
            $templateId = $cartItem->getProductOption()->getExtensionAttributes()->getTemplateId();
            if (is_array($additionalOptions)) {
                $options = ['additional_options' => [], 'location_id' => $locationId, 'template_id' => $templateId];
                foreach ($additionalOptions as $option) {
                    $options['additional_options'][] = [
                        "option_title" => $option->getOptionTitle(),
                        "option_label" => $option->getOptionLabel(),
                        "option_sku" => $option->getOptionSku(),
                        "base_sku" => $option->getBaseSku()
                    ];
                }
                return $this->objectFactory->create($options);
            }
        }
        return null;
    }

    public function processOptions(CartItemInterface $cartItem)
    {
        try {
            $options = [];
            $additionalOptions = $cartItem->getBuyRequest()->getAdditionalOptions();
            if (is_array($additionalOptions) && !empty($additionalOptions)) {
                foreach ($additionalOptions as $option) {
                    if(empty($option)){
                         continue;
                    }
                    $additionalOption = $this->additionOptionFactory->create();
                    $additionalOption->setOptionTitle($option['option_title']);
                    $additionalOption->setOptionLabel($option['option_label']);
                    $additionalOption->setOptionSku($option['option_sku']);
                    $additionalOption->setBaseSku($option['base_sku']);
                    $options[] = $additionalOption;
                }
                $productOption = $cartItem->getProductOption()
                    ? $cartItem->getProductOption()
                    : $this->productOptionFactory->create();
                $extensibleAttribute = $productOption->getExtensionAttributes()
                    ? $productOption->getExtensionAttributes()
                    : $this->productOptionExtensionFactory->create();
                $extensibleAttribute->setAdditionalOptions($options);
                $productOption->setExtensionAttributes($extensibleAttribute);
                $cartItem->setProductOption($productOption);
            }
            return $cartItem;
        } catch (\Exception $e) {
            
        }
        
    }
}
