<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Customertax extends \Magento\Tax\Model\TaxClass\Source\Customer
{
    public function __construct(
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        parent::__construct(
            $taxClassRepository,
            $searchCriteriaBuilder,
            $filterBuilder
        );
    }


    public function toOptionArray()
    {
        $data = $this->getAllOptions();
        $options[] = array('value' => '', 'label' => '');
        foreach ($data as $option) {
            $options[] = array(
                'value' => $option['label'],
                'label' => $option['label']
            );
        }


        return $options;
    }

}
