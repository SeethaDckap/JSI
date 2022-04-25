<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Model\Eav\Attribute\Data\Customer;

class AccessRightsOptions extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean {

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
    public function getAllOptions() {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => __('ERP Account Default'),
                    'value' => 2
                ),
                array(
                    'label' => __('Disabled'),
                    'value' => 0
                ),
                array(
                    'label' => __('Access Role'),
                    'value' => 1
                )
            );
        }
        return $this->_options;
    }

}
