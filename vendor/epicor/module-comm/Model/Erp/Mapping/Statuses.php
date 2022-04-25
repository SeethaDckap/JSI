<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Mapping;


class Statuses extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $salesResourceModelOrderStatusCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $salesResourceModelOrderStatusCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->salesResourceModelOrderStatusCollectionFactory = $salesResourceModelOrderStatusCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function toOptionArray()
    {
        return array_merge(
            array('' => ''), $this->salesResourceModelOrderStatusCollectionFactory->create()->toOptionArray()
        );
    }

}
