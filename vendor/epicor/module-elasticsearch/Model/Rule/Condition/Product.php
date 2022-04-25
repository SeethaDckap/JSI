<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\Rule\Condition;

use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory as ProductModelFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\Locale\FormatInterface;
use Magento\Rule\Model\Condition\Context;
use Epicor\Elasticsearch\Model\Rule\Condition\Product\AttributeList;
use Epicor\Elasticsearch\Model\Rule\Condition\Product\QueryBuilder;
/**
 * Product attribute search engine rule.
 *
 */
class Product extends \Magento\Rule\Model\Condition\Product\AbstractProduct
{
    /**
     * @var AttributeList
     */
    private $attributeList;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * Constructor.
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param Context $context
     * @param Data $backendData
     * @param Config $config
     * @param AttributeList $attributeList
     * @param QueryBuilder $queryBuilder
     * @param ProductModelFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ProductResource $productResource
     * @param Collection $attrSetCollection
     * @param FormatInterface $localeFormat
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendData,
        Config $config,
        AttributeList $attributeList,
        QueryBuilder $queryBuilder,
        ProductModelFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        ProductResource $productResource,
        Collection $attrSetCollection,
        FormatInterface $localeFormat,
        array $data = []
    ) {
        $this->attributeList = $attributeList;
        $this->queryBuilder = $queryBuilder;
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data
        );
    }

    /**
     * {@inheritDoc}
     */
    public function loadAttributeOptions()
    {
        $attributes        = [];
        $productAttributes = [];
        foreach ($this->attributeList->getAttributeCollection() as $attribute) {
            if ($attribute->getFrontendLabel()) {
                $label = $attribute->getFrontendLabel();
                $productAttributes[$attribute->getAttributeCode()] = $label;
            }
        }
        asort($productAttributes);
        $attributes += $productAttributes;
        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Set the target element name (name of the input into the form).
     *
     * @param string $elementName Target element name
     *
     * @return $this
     */
    public function setElementName($elementName)
    {
        $this->elementName = $elementName;
        return $this;
    }

    /**
     * Build a search query for the current rule.
     *
     * @return array
     */
    public function getSearchQuery()
    {
        return $this->queryBuilder->getSearchQuery($this);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * {@inheritDoc}
     */
    public function getInputType()
    {
        $inputType        = 'string';
        $selectAttributes = ['attribute_set_id'];

        if (in_array($this->getAttribute(), $selectAttributes)) {
            $inputType = 'select';
        } elseif ($this->getAttribute() === 'price') {
            $inputType = 'numeric';
        } elseif (is_object($this->getAttributeObject())) {
            $frontendInput = $this->getAttributeObject()->getFrontendInput();
            $frontendClass = $this->getAttributeObject()->getFrontendClass();
            if ($this->getAttributeObject()->getAttributeCode() === 'category_ids') {
                $inputType = 'category';
	    } elseif ($this->getAttributeObject()->getAttributeCode() === 'sku') {
                $inputType = 'sku';
            } elseif (in_array($frontendInput, ['select', 'multiselect'])) {
                $inputType = 'multiselect';
            } elseif (in_array($frontendClass, ['validate-digits', 'validate-number'])) {
                $inputType = 'numeric';
            } elseif ($frontendInput === 'date') {
                $inputType = 'date';
            } elseif ($frontendInput === 'boolean') {
                $inputType = 'boolean';
            }
        }
        return $inputType;
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        $valueElementType = 'text';
        if ($this->getAttribute() == 'attribute_set_id') {
            $valueElementType = 'select';
        } elseif (is_object($this->getAttributeObject())) {
            $frontendInput = $this->getAttributeObject()->getFrontendInput();
            if ($frontendInput === 'boolean') {
                $valueElementType = 'select';
            } elseif ($frontendInput === 'date') {
                $valueElementType = 'date';
            } elseif (in_array($frontendInput, ['select', 'multiselect'])) {
                $valueElementType = 'multiselect';
            }
        }
        return $valueElementType;
    }

    /**
     * {@inheritDoc}
     */
    public function getValueName()
    {
        $valueName = parent::getValueName();
        return $valueName;
    }

    /**
     * {@inheritDoc}
     */
    public function getOperatorName()
    {
        $operatorName = parent::getOperatorName();
        return $operatorName;
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = [
                'string'      => ['{}', '!{}'],
                'numeric'     => ['==', '!=', '>=', '>', '<=', '<'],
                'date'        => ['==', '>=', '>', '<=', '<'],
                'select'      => ['==', '!='],
                'boolean'     => ['==', '!='],
                'multiselect' => ['()', '!()'],
                'grid'        => ['()', '!()'],
		'category'    => ['()', '!()'],
		'sku'    => ['()', '!()']
            ];
            $this->_arrayInputTypes            = ['multiselect', 'grid', 'category'];
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue()
    {
        return $this->getData('value');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.ElseExpression)
     * {@inheritDoc}
     */
    protected function _prepareValueOptions()
    {
        parent::_prepareValueOptions();
    }
}
