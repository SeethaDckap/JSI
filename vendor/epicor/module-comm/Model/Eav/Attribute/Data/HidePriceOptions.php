<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Eav\Attribute\Data;


class HidePriceOptions extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity
    ) {
        parent::__construct(
            $eavAttrEntity
        );
    }

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => __('No'),
                    'value' => 0
                ),
                array(
                    'label' => __('Yes'),
                    'value' => 1
                ),
                array(
                    'label' => __('Show Default'),
                    'value' => 2
                ),
                array(
                    'label' => __('Yes Checkout'),
                    'value' => 3
                ),
            );
        }
        return $this->_options;
    }

}
