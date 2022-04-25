<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Search\Observer;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $catalogResourceModelProductAttributeCollectionFactory;

    protected $eavEntityAttributeFactory;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $catalogResourceModelProductAttributeCollectionFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $eavEntityAttributeFactory
    ) {
        $this->catalogResourceModelProductAttributeCollectionFactory = $catalogResourceModelProductAttributeCollectionFactory;
        $this->eavEntityAttributeFactory = $eavEntityAttributeFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }



}

