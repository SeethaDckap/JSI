<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Locationlist
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $commResourceLocationCollectionFactory;

    public function __construct(
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory
    ) {
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
    }
    public function toOptionArray()
    {
        $locations = $this->commResourceLocationCollectionFactory->create();
        $erps = array();
        $locs = []; 
        $locs[] = array('value' => '', 'label' => ' ');
        foreach ($locations as $loc) {
            $locs[] = array('value' => $loc->getCode(), 'label' => $loc->getName());
        }
        return $locs;
    }

}
