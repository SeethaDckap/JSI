<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Catalog\Product;


/**
 * Product option model override
 * 
 * Adds custom types needed by EWA
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Option extends \Magento\Catalog\Model\Product\Option
{

    const OPTION_GROUP_ECC_TEXT = 'ecc_text';
    const OPTION_GROUP_EWA = 'ewa';
    const OPTION_TYPE_EWA_CODE = 'ewa_code';
    const OPTION_TYPE_EWA_TITLE = 'ewa_title';
    const OPTION_TYPE_EWA_SHORT_DESCRIPTION = 'ewa_short_description';
    const OPTION_TYPE_EWA_DESCRIPTION = 'ewa_description';
    const OPTION_TYPE_EWA_SKU = 'ewa_sku';
    const OPTION_TYPE_ECC_TEXT = 'ecc_text_field';

    /**
     * @var \Epicor\Comm\Model\Catalog\Product\Option\Type\EwaFactory
     */
    protected $commCatalogProductOptionTypeEwaFactory;

    /**
     * @var \Epicor\Comm\Model\Catalog\Product\Option\Type\Ecc\TextFactory
     */
    protected $commCatalogProductOptionTypeEccTextFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Catalog\Model\Product\Option\Value $productOptionValue,
        \Magento\Catalog\Model\Product\Option\Type\Factory $optionFactory,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Model\Product\Option\Validator\Pool $validatorPool,
        \Epicor\Comm\Model\Catalog\Product\Option\Type\EwaFactory $commCatalogProductOptionTypeEwaFactory,
        \Epicor\Comm\Model\Catalog\Product\Option\Type\Ecc\TextFactory $commCatalogProductOptionTypeEccTextFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commCatalogProductOptionTypeEwaFactory = $commCatalogProductOptionTypeEwaFactory;
        $this->commCatalogProductOptionTypeEccTextFactory = $commCatalogProductOptionTypeEccTextFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $productOptionValue,
            $optionFactory,
            $string,
            $validatorPool,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * Get group name of option by given option type
     *
     * @param string $type
     * @return string
     */
    public function getGroupByType($type = null)
    {
        if (is_null($type)) {
            $type = $this->getType();
        }

        $group = parent::getGroupByType($type);
        if ($group === '' && in_array($type, array(
                self::OPTION_TYPE_EWA_CODE,
                self::OPTION_TYPE_EWA_TITLE,
                self::OPTION_TYPE_EWA_SHORT_DESCRIPTION,
                self::OPTION_TYPE_EWA_DESCRIPTION,
                self::OPTION_TYPE_EWA_SKU,
            ))) {
            $group = self::OPTION_GROUP_EWA;
        }

        if ($group === '' && in_array($type, array(
                self::OPTION_TYPE_ECC_TEXT,
            ))) {
            $group = self::OPTION_GROUP_ECC_TEXT;
        }

        return $group;
    }

    /**
     * Group model factory
     *
     * @param string $type Option type
     * @return Mage_Catalog_Model_Product_Option_Group_Abstract
     */
    public function groupFactory($type)
    {
        if (in_array($type, array(
                self::OPTION_TYPE_EWA_CODE,
                self::OPTION_TYPE_EWA_TITLE,
                self::OPTION_TYPE_EWA_SHORT_DESCRIPTION,
                self::OPTION_TYPE_EWA_DESCRIPTION,
                self::OPTION_TYPE_EWA_SKU,
            ))) {
            return $this->commCatalogProductOptionTypeEwaFactory->create();
        }

        if (in_array($type, array(
                self::OPTION_TYPE_ECC_TEXT,
            ))) {
            return $this->commCatalogProductOptionTypeEccTextFactory->create();
        }

        return parent::groupFactory($type);
    }

    public function deleteOptionsArray()
    {
        unset($this->_options);
        $this->_options = array();
    }

}
