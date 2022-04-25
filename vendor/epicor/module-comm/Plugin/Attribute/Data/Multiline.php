<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Attribute\Data;


class Multiline
{

    /**
     * @var \Magento\Eav\Model\Attribute\Data\Text
     */
    protected $textatt;


    public function __construct(
        \Magento\Eav\Model\Attribute\Data\Text $textatt
    ) {
        $this->textatt = $textatt;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function aroundValidateValue(
        \Magento\Eav\Model\Attribute\Data\Multiline $subject,
        \Closure $proceed,
        $value
    ) {
        $attrCode = $subject->getAttribute()->getAttributeCode();
         if($attrCode == 'street'){
           $errors = [];
            $lines = $this->processValue($value);
            $attribute = $subject->getAttribute();
            $attributeLabel = __($attribute->getStoreLabel());
            if ($attribute->getIsRequired() && empty($lines)) {
                $errors[] = __('"%1" is a required value.', $attributeLabel);
            }

            $maxAllowedLineCount = $attribute->getMultilineCount();
            if (count($lines) > $maxAllowedLineCount) {
               // $errors[] = __('"%1" cannot contain more than %2 lines.', $attributeLabel, $maxAllowedLineCount);
            }

            foreach ($lines as $lineIndex => $line) {
                // First line must be always validated
                if ($lineIndex == 0 || !empty($line)) {
                    $this->textatt->setAttribute($attribute);
                    $result = $this->textatt->validateValue($line);
                    if ($result !== true) {
                        $errors = array_merge($errors, $result);
                    }
                }
            }

            return (count($errors) == 0) ? true : $errors;
        }
        return $proceed($value);
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