<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Model\Eav\Attribute\Data;


/**
 * Branchpickup 
 *
 * @category   Epicor
 * @package    Epicor_Branchpickup
 * @author     Epicor Websales Team
 */
class Locationoptions extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
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
    public function toOptionArray()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => __('Yes'),
                    'value' => 1
                ),
                array(
                    'label' => __('No'),
                    'value' => 0
                ),
                array(
                    'label' => __('B2B'),
                    'value' => 3
                ),
                array(
                    'label' => __('B2C'),
                    'value' => 2
                ),
            );
        }
        return $this->_options;
    }

}
