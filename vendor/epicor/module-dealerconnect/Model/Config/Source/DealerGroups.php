<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Config\Source;


class DealerGroups
{
    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\CollectionFactory
     */
    protected $dealerGroupsResourceModelCollectionFactory;

    public function __construct(
        \Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\CollectionFactory $dealerGroupsResourceModelCollectionFactory
    ) {
        $this->dealerGroupsResourceModelCollectionFactory = $dealerGroupsResourceModelCollectionFactory;
    }


    public function toOptionArray()
    {
        $arr = array();
        $collection = $this->dealerGroupsResourceModelCollectionFactory->create();
        $collection->filterActive();
        foreach ($collection as $group) {
            $arr[] = array('label' => $group->getTitle(), 'value' => $group->getId());
        }
        return $arr;

    }

}
