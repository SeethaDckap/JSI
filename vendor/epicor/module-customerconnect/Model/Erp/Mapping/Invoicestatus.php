<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Erp\Mapping;


class Invoicestatus extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    /**
     * @var \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Invoicestatus\CollectionFactory
     */
    protected $customerconnectResourceErpMappingInvoicestatusCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Invoicestatus\CollectionFactory $customerconnectResourceErpMappingInvoicestatusCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->customerconnectResourceErpMappingInvoicestatusCollectionFactory = $customerconnectResourceErpMappingInvoicestatusCollectionFactory;
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
        $this->_init('Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Invoicestatus');
    }

    public function getDescription()
    {
        if (empty($this->_description)) {
            $collection = $this->customerconnectResourceErpMappingInvoicestatusCollectionFactory->create();
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
