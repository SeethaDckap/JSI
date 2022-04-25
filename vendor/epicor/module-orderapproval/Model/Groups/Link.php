<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Groups;

use Magento\Framework\Model\AbstractModel;
use Epicor\OrderApproval\Api\Data\LinkInterface;

class Link extends AbstractModel implements LinkInterface
{
    public function _construct()
    {
        $this->_init('Epicor\OrderApproval\Model\ResourceModel\Groups\Link');
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ID);
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
    public function getParentGroupId()
    {
        return parent::getData(self::PARENT_GROUP_ID);
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
     * @param int $id
     *
     * @return \Epicor\OrderApproval\Api\Data\LinkInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set Group Id.
     *
     * @param int $groupId
     *
     * @return \Epicor\OrderApproval\Api\Data\LinkInterface
     */
    public function setGroupId($groupId)
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    /**
     * Set Parent Group Id.
     *
     * @param int $parentGroupId
     *
     * @return \Epicor\OrderApproval\Api\Data\CustomerInterface
     */
    public function setParentGroupId($parentGroupId)
    {
        return $this->setData(self::PARENT_GROUP_ID, $parentGroupId);
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
