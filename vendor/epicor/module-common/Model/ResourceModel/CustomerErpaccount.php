<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomerErpaccount extends AbstractDb
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->_logger = $logger;
    }

    protected function _construct()
    {
        $this->_init('ecc_customer_erp_account', 'id');
    }

    /**
     * Save ACL resources
     *
     * @param \Magento\Authorization\Model\Rules $rule
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveRel(\Epicor\Common\Model\CustomerErpaccount $erpaccount)
    {
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $insertData = $this->_prepareDataForTable(
                new \Magento\Framework\DataObject($erpaccount->getData()),
                $this->getMainTable()
            );
            $connection->insertOnDuplicate($this->getMainTable(), $insertData);
            $connection->commit();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->_logger->critical($e);
        }
    }

    public function getErpAcctCounts(\Epicor\Common\Model\CustomerErpaccount $erpaccount)
    {
        $connection = $this->getConnection();
        $sql = $connection->select()
            ->from($this->getMainTable());
        if ($erpaccount->getData('customer_id')) {
            $sql = $sql->where('customer_id = ?', $erpaccount->getData('customer_id'));
        }
        if ($erpaccount->getData('erp_account_id')) {
            $sql = $sql->where('erp_account_id = ?', $erpaccount->getData('erp_account_id'));
        }
        if ($erpaccount->getData('is_favourite')) {
            $sql = $sql->where('is_favourite = ?', $erpaccount->getData('is_favourite'));
        }
        $result = $connection->fetchAll($sql);
        return $result;
    }

    public function deleteByErpId(\Epicor\Common\Model\CustomerErpaccount $erpaccount)
    {
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            if (true) {
                $condition = ['customer_id = ?' => $erpaccount->getCustomerId(),
                    'erp_account_id = ?' => $erpaccount->getErpAccountId()];

                $connection->delete($this->getMainTable(), $condition);
                $connection->commit();
            }


        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->_logger->critical($e);
        }
    }

    public function getAllErpAcctids(\Epicor\Common\Model\CustomerErpaccount $erpaccount)
    {
        $connection = $this->getConnection();
        $sql = $connection->select()
            ->from($this->getMainTable(), 'erp_account_id');
        if ($erpaccount->getData('customer_id')) {
            $sql = $sql->where('customer_id = ?', $erpaccount->getData('customer_id'));
        }
        if ($erpaccount->getData('erp_account_id')) {
            $sql = $sql->where('erp_account_id = ?', $erpaccount->getData('erp_account_id'));
        }
        $result = $connection->fetchCol($sql);
        return $result;
    }

    public function updateByCustomerId(\Epicor\Common\Model\CustomerErpaccount $erpaccount)
    {
        $connection = $this->getConnection();
        try {
            $isExisting = $erpaccount->getAllErpAcctids();
            if (empty($isExisting)) {
                $connection->beginTransaction();
                $UpdateData = $this->_prepareDataForTable(
                    new \Magento\Framework\DataObject($erpaccount->getData()),
                    $this->getMainTable()
                );
                $condition = ['customer_id = ?' => $erpaccount->getCustomerId()];

                $connection->update($this->getMainTable(), $UpdateData, $condition);
                $connection->commit();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->_logger->critical($e);
        }
    }

    public function updateFavourite(\Epicor\Common\Model\CustomerErpaccount $erpaccount)
    {
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $data = ['is_favourite' => 0];
            $UpdateData = $this->_prepareDataForTable(
                new \Magento\Framework\DataObject($data),
                $this->getMainTable()
            );
            $condition = ['customer_id = ?' => $erpaccount->getCustomerId()];
            $connection->update($this->getMainTable(), $UpdateData, $condition);

            $newdata = ['is_favourite' => 1];
            $newUpdateData = $this->_prepareDataForTable(
                new \Magento\Framework\DataObject($newdata),
                $this->getMainTable()
            );
            $newcondition = ['customer_id = ?' => $erpaccount->getCustomerId(),
                'erp_account_id = ?' => $erpaccount->getErpAccountId()];
            $connection->update($this->getMainTable(), $newUpdateData, $newcondition);


            $connection->commit();

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->_logger->critical($e);
        }
    }

    public function unselectFavourite(\Epicor\Common\Model\CustomerErpaccount $erpaccount)
    {
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $data = ['is_favourite' => 0];
            $UpdateData = $this->_prepareDataForTable(
                new \Magento\Framework\DataObject($data),
                $this->getMainTable()
            );
            $condition = ['customer_id = ?' => $erpaccount->getCustomerId()];
            $connection->update($this->getMainTable(), $UpdateData, $condition);
            $connection->commit();

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->_logger->critical($e);
        }
    }
}
