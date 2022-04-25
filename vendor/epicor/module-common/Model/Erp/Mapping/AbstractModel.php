<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Erp\Mapping;


class AbstractModel extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function loadMappingByStore($value, $field, $store_id = null)
    {
        $store_id = is_null($store_id) ? $this->storeManager->getStore()->getStoreId() : $store_id;

        $item = $this->getCollection()->addFieldToFilter($field, $value)->addFieldToFilter('store_id', $store_id)->getFirstItem();
        if ($store_id != 0 && is_null($item->getId())) {
            $item = $this->getCollection()->addFieldToFilter($field, $value)->addFieldToFilter('store_id', 0)->getFirstItem();
        }
        return $this->load($item->getId());
    }

    public function loadAllMappingByStore($value, $field, $returnVal, $store_id = null)
    {
        $store_id = is_null($store_id) ? $this->storeManager->getStore()->getStoreId() : $store_id;
        $item = array();
        $item = $this->getCollection()->addFieldToFilter($field, $value)->addFieldToFilter('store_id', $store_id)->getColumnValues($returnVal);

        if ($store_id != 0 && empty($item)) {
            $item = $this->getCollection()->addFieldToFilter($field, $value)->addFieldToFilter('store_id', 0)->getColumnValues($returnVal);
        }
        return $item;
    }

}
