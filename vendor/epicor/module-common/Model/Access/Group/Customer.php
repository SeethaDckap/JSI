<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Access\Group;


/**
 * 
 * Access Group Customer model
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Customer extends \Epicor\Database\Model\Access\Group\Customer
{

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Group\Customer\CollectionFactory
     */
    protected $commonResourceAccessGroupCustomerCollectionFactory;

    /**
     * @var \Epicor\Common\Model\Access\Group\CustomerFactory
     */
    protected $commonAccessGroupCustomerFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Model\ResourceModel\Access\Group\Customer\CollectionFactory $commonResourceAccessGroupCustomerCollectionFactory,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commonResourceAccessGroupCustomerCollectionFactory = $commonResourceAccessGroupCustomerCollectionFactory;
        $this->commonAccessGroupCustomerFactory = $commonAccessGroupCustomerFactory;
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
        $this->_init('Epicor\Common\Model\ResourceModel\Access\Group\Customer');
    }

    public function getCustomerAccessGroups($customerId)
    {
        $collection = $this->commonResourceAccessGroupCustomerCollectionFactory->create();
        $collection->addFilter('customer_id', $customerId);
        return $collection->getItems();
    }

    /**
     * Updates a contact with the provided access groups
     * 
     * @param integer $customerId
     * @param array $groupIds
     */
    public function updateCustomerAccessGroups($customerId, $groupIds)
    {
        // delete all linked groups first
        $items = $this->getCustomerAccessGroups($customerId);

        $existing = array();

        //delete existing.
        foreach ($items as $group) {
            if (!in_array($group->getGroupId(), $groupIds)) {
                $group->delete();
            } else {
                $existing[] = $group->getGroupId();
            }
        }

        // add groups to customer
        foreach ($groupIds as $groupId) {
            if (!in_array($groupId, $existing)) {
                $model = $this->commonAccessGroupCustomerFactory->create();
                $model->setCustomerId($customerId);
                $model->setGroupId($groupId);
                $model->save();
            }
        }
    }

}
