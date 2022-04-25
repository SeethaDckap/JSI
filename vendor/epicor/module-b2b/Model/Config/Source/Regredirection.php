<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Model\Config\Source;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

class Regredirection
{

    /**
     * @var CollectionFactory
     */
    protected $cmsResourceModelPageCollectionFactory;

    /**
     * Regredirection constructor.
     * @param CollectionFactory $cmsResourceModelPageCollectionFactory
     */
    public function __construct(
        CollectionFactory $cmsResourceModelPageCollectionFactory
    ) {
        $this->cmsResourceModelPageCollectionFactory = $cmsResourceModelPageCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $successRedirection = $this->cmsResourceModelPageCollectionFactory->create()
            ->load()->toOptionIdArray();
        $emptyValue = array('value' => '', 'label' => '');
        array_unshift($successRedirection, $emptyValue);
        return $successRedirection;
    }
}
