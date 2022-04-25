<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Customer\Address;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

class DataProvider
{
    /**
     * |
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * DataProvider constructor.
     * @param \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory
    )
    {
        $this->addressCollectionFactory = $addressCollectionFactory;
    }

    public function afterGetData(
        \Magento\Customer\Model\Customer\DataProvider $subject,
        $result
    )
    {
        $collection = $subject->getCollection();
        $items = $collection->getItems();
        foreach ($items as $customer) {
            $erpAccounts = $customer->getErpAcctCounts();
            if (count($erpAccounts) > 1) {
                $customerId = $customer->getId();
                $collection = $this->addressCollectionFactory->create();
                $collection->addAttributeToSelect('entity_id')
                    ->setCustomerFilter([$customerId])
                    ->addAttributeToFilter('ecc_erp_group_code', ['null' => true], 'left');
                $addressess = $collection->getColumnValues('entity_id');
                foreach ($result[$customerId]['address'] as $addresId => $address) {
                    if (!in_array($addresId, $addressess)) {
                        unset($result[$customerId]['address'][$addresId]);
                    }
                }
            }
        }
        return $result;
    }
}