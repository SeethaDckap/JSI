<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Producttax extends \Magento\Tax\Model\TaxClass\Source\Product
{
    public function __construct(
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $classesFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $optionFactory,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        parent::__construct(
            $classesFactory,
            $optionFactory,
            $taxClassRepository,
            $searchCriteriaBuilder,
            $filterBuilder
        );
    }


    public function toOptionArray($hasBlank = false)
    {
        $data = $this->getAllOptions();

        $options = array();
        if ($hasBlank) {
            $options[] = array(
                'value' => '',
                'label' => ''
            );
        }
        foreach ($data as $option) {
            $options[] = array(
                'value' => $option['label'],
                'label' => $option['label']
            );
        }

        return $options;
    }

}
