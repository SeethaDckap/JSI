<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Eav\Attribute\Data;


class Eccproducttype extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
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
                    'label' => __('  '),
                    'value' => ''
                ),                
                array(
                    'label' => __('Simple product'),
                    'value' => 'S'
                ),
                array(
                    'label' => __('Extended kit (exploded parts)'),
                    'value' => 'E'
                ),
                array(
                    'label' => __('EWA configurator product'),
                    'value' => 'C'
                ),
                array(
                    'label' => __('EWC configurator product'),
                    'value' => 'K'
                ),
                array(
                    'label' => __('Parts explosion'),
                    'value' => 'P'
                ),                
            );
        }
        return $this->_options;
    }

}
