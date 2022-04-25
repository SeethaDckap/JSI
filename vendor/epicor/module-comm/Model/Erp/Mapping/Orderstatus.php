<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Mapping;


class Orderstatus extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    private $_description;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $salesResourceModelOrderStatusCollectionFactory;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $salesResourceModelOrderStatusCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->salesResourceModelOrderStatusCollectionFactory = $salesResourceModelOrderStatusCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\ResourceModel\Erp\Mapping\Orderstatus');
    }

    /**
     * Get Order status Code
     * @method getCode()
     * @return string 
     */
    public function getDescription()
    {
        if (empty($this->_description)) {
            $collection = $this->salesResourceModelOrderStatusCollectionFactory->create();
            /* @var $collection \Magento\Sales\Model\ResourceModel\Order\Status\Collection */
            $collection->addFieldToFilter('status', $this->getStatus());
            if ($collection->count() > 0) {
                $this->_description = $collection->getFirstItem()->getStoreLabel();
            }
        }
        return $this->_description;
    }

    public function toOptionArray()
    {
        $arr = array();
        $items = $this->getCollection()->getItems();
        foreach ($items as $item) {
            $arr[] = array('value' => $item->getCode(), 'label' => $item->getCode());
        }
        return $arr;
    }

    public function toGridArray()
    {
        $arr = array();
        $items = $this->getCollection()->getItems();
        foreach ($items as $item) {
            $arr[$item->getCode()] = $item->getCode();
        }
        return $arr;
    }

}
