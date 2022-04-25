<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Contract;


/**
 * Model Class for Contract Address
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Address extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory
     */
    protected $listsResourceListModelAddressCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory $listsResourceListModelAddressCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->listsResourceListModelAddressCollectionFactory = $listsResourceListModelAddressCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\ListModel\Address');
    }

    /**
     * Retrives Addresses from the contract list(for a specific customer)
     * 
     * @return array $items
     */
    public function getCustomerAddresses($listid)
    {
        $customer = $this->customerSession->getCustomer();
        $customerId = $customer->getId();
        /* @var $collection Epicor_Lists_Model_Resource_List_Address_Collection */
        $collection = $this->listsResourceListModelAddressCollectionFactory->create();
        $collection->getSelect()->join(
            array('list' => $collection->getTable('ecc_list_customer')), 'list.customer_id = ' . $customerId . ' AND list.list_id = main_table.list_id', array()
        );
        $collection->addFieldtoFilter('main_table.list_id', $listid);
        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $item;
        }
        return $items;
    }

}
