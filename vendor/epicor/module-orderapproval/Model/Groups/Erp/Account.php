<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Groups\Erp;

use Epicor\OrderApproval\Api\Data\ErpAccountInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Account
 *
 * @package Epicor\OrderApproval\Model\Groups\Erp
 */
class Account extends AbstractModel implements ErpAccountInterface
{
    public function _construct()
    {
        $this->_init(
            'Epicor\OrderApproval\Model\ResourceModel\Groups\Erp\Account'
        );
    }

    /**
     * Get Group Id.
     *
     * @return int
     */
    public function getGroupId()
    {
        return parent::getData(self::GROUP_ID);
    }

    /**
     * Get  ERP Account Id.
     *
     * @return int
     */
    public function getErpAccountId()
    {
        return parent::getData(self::ERP_ACCOUNT_ID);
    }

    /**
     * Set Group Id.
     *
     * @param string $groupId
     *
     * @return ErpAccountInterface
     */
    public function setGroupId($groupId)
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    /**
     * Set ERP Account Id.
     *
     * @param string $erpAccountId
     *
     * @return ErpAccountInterface
     */
    public function setErpAccountId($erpAccountId)
    {
        return $this->setData(self::ERP_ACCOUNT_ID, $erpAccountId);
    }
}
