<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Service;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AttributeOptions
 * @package Epicor\Comm\Service
 */
class AttributeOptions
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var AttributeOptionManagementInterface
     */
    private $attributeOptionManagement;

    /**
     * @var AttributeOptionInterface
     */
    private $attributeOption;

    /**
     * @var Action
     */
    private $action;

    /**
     * AttributeOptions constructor.
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     * @param AttributeOptionInterface $attributeOption
     * @param Action $action
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionManagementInterface $attributeOptionManagement,
        AttributeOptionInterface $attributeOption,
        Action $action
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->attributeOption = $attributeOption;
        $this->action = $action;
    }

    /**
     * @param $attributeCode
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]|null
     */
    private function getoptions($attributeCode)
    {
        try {
            return $this->attributeRepository
                    ->get(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
                    ->getOptions();
        } catch (NoSuchEntityException $noSuchEntityException) {
            //Log Exception
        }
    }

    /**
     * @param $attributeCode
     * @return array
     */
    public function getOptionsValues($attributeCode)
    {
        $options = $this->getOptions($attributeCode);

        $values = array();
        foreach ($options as $option) {
            $values[$option['label']] = $option['value'];
        }

        return $values;
    }

    /**
     * @param $attributeCode
     * @param $label
     * @return mixed
     */
    public function getOptionLabelValue($attributeCode, $label)
    {
        $values = $this->getOptionsValues($attributeCode);

        if (array_key_exists($label, $values)) {
            return $values[$label];
        }

        return [];
    }

    /**
     * @param $attributeCode
     * @param $value
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function addAttributeOption($attributeCode, $value)
    {
        $option = $this->attributeOption;
        $option->setLabel($value);
        return $this->attributeOptionManagement->add(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            $option
        );
    }
}
