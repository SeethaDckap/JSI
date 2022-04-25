<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Config\Source;


class Lists
{

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    protected $listsResourceListModelCollectionFactory;

    public function __construct(
        \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory
    ) {
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
    }
    public function toOptionArray($includeExtraOptions = true)
    {
        $lists = $this->listsResourceListModelCollectionFactory->create();
        $lists->getSelect()->order('erp_code');

        $options = array();
        foreach ($lists as $list) {
            $options[] = array(
                'label' => $list->getErpCode() . " - " . $list->getTitle(),
                'value' => $list->getId()
            );
        }


        return $options;
    }

}
