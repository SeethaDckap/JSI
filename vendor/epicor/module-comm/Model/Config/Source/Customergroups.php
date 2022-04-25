<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminNotificationModelInbox
 *
 * @author David.Wylie
 */
class Customergroups
{

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $customerResourceModelGroupCollectionFactory;

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerResourceModelGroupCollectionFactory
    ) {
        $this->customerResourceModelGroupCollectionFactory = $customerResourceModelGroupCollectionFactory;
    }
    public function toOptionArray()
    {
        $options = array();
        $groups = $this->customerResourceModelGroupCollectionFactory->create();
        foreach ($groups as $key => $value) {
            $options[] = array('value' => $key, 'label' => $value->getCustomerGroupCode());
        }
        return $options;
    }

}
