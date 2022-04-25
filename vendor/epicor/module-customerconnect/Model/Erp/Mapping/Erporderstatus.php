<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Erp\Mapping;


class Erporderstatus extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    /**
     * @var \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Erporderstatus\CollectionFactory
     */
    protected $customerconnectResourceErpMappingErporderstatusCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Erporderstatus\CollectionFactory $customerconnectResourceErpMappingErporderstatusCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->customerconnectResourceErpMappingErporderstatusCollectionFactory = $customerconnectResourceErpMappingErporderstatusCollectionFactory;
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
        $this->_init('Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Erporderstatus');
    }

    public function getDescription()
    {
        if (empty($this->_description)) {
            $collection = $this->customerconnectResourceErpMappingErporderstatusCollectionFactory->create();
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
            $arr[$item->getCode()] = $item->getStatus();
        }
        return $arr;
    }

}
