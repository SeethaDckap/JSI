<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model\Eav\Attribute\Data;

class Yesnonulloption extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{

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
                    'label' => __('Sales Rep Account Default'),
                    'value' => null
                ),
                array(
                    'label' => __('Yes'),
                    'value' => 'Y'
                ),
                array(
                    'label' => __('No'),
                    'value' => 'N'
                ),
            );
        }
        return $this->_options;
    }

}
