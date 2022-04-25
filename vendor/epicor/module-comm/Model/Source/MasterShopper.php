<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Source;


/**
 * Class Message Types
 */
class MasterShopper implements \Magento\Framework\Option\ArrayInterface
{
    
    const YES = 1;
    const NO = 0;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::YES,
                'label' => __('Yes')
            ],
            [
                'value' => self::NO,
                'label' => __('No')
            ]
        ];
    }
}
