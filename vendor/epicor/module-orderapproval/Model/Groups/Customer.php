<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Groups;

use Magento\Framework\Model\AbstractModel;
use Epicor\OrderApproval\Api\Data\CustomerInterface;

class Customer extends AbstractModel implements CustomerInterface
{
    public function _construct()
    {
        $this->_init('Epicor\OrderApproval\Model\ResourceModel\Groups\Customer');
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getGroupId()
    {
        return parent::getData(self::GROUP_ID);
    }

    /**
     * Get Customer ID.
     *
     * @return int
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * Get By Group.
     *
     * @return int
     */
    public function getByGroup()
    {
        return parent::getData(self::BY_GROUP);
    }

    /**
     * Get By Customer.
     *
     * @return int
     */
    public function getByCustomer()
    {
        return parent::getData(self::BY_CUSTOMER);
    }

    /**
     * Set Group Id.
     *
     * @param int $groupId
     *
     * @return \Epicor\OrderApproval\Api\Data\CustomerInterface
     */
    public function setGroupId($groupId)
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    /**
     * Set Customer Id.
     *
     * @param int $customerId
     *
     * @return \Epicor\OrderApproval\Api\Data\CustomerInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Set By Group.
     *
     * @param int $byGroup
     *
     * @return \Epicor\OrderApproval\Api\Data\CustomerInterface
     */
    public function setByGroup($byGroup)
    {
        return $this->setData(self::BY_GROUP, $byGroup);
    }

    /**
     * Set By Customer
     *
     * @param int $byCustomer
     *
     * @return \Epicor\OrderApproval\Api\Data\CustomerInterface
     */
    public function setByCustomer($byCustomer)
    {
        return $this->setData(self::BY_CUSTOMER, $byCustomer);
    }
}
