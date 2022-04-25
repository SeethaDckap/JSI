<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Catalog\Product\Edit\Tab\Options;


/**
 * Product option edit tab override
 * 
 * Adds new field types for EWA
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Option extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Option
{

    /**
     * Class constructor
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\Config\Source\Yesno $configYesNo,
        \Magento\Catalog\Model\Config\Source\Product\Options\Type $optionType,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $configYesNo,
            $optionType,
            $product,
            $registry,
            $productOptionConfig,
            $data
        );
        $this->setTemplate('epicor_comm/catalog/product/edit/options/option.phtml');
    }

    /**
     * Retrieve html templates for different types of product custom options
     *
     * @return string
     */
    public function getTemplatesHtml()
    {
        $templates = parent::getTemplatesHtml();

        $templates .= "\n" . $this->getChildHtml('ewa_option_type');
        $templates .= "\n" . $this->getChildHtml('ecc_text_option_type');

        return $templates;
    }

    public function getOptionValues()
    {
        parent::getOptionValues();

        if (!empty($this->_values)) {

            $optionsArr = array_reverse($this->getProduct()->getOptions(), true);
            $options = array();
            foreach ($optionsArr as $option) {
                $options[$option->getOptionId()] = $option;
            }

            $values = array();

            foreach ($this->_values as $option) {

                $pOption = $options[$option->getId()];

                $option->setEccCode($pOption->getEccCode());
                $option->setEccDefaultValue($pOption->getEccDefaultValue());
                $option->setEccValidationCode($pOption->getEccValidationCode());

                $values[] = $option;
            }

            $this->_values = $values;
        }

        return $this->_values;
    }

}
