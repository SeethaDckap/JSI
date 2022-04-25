<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
/**
 * Claim Status resource model
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Claimstatus extends AbstractDb
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Claimstatus constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        $connectionName = null
    )
    {
        $this->_logger = $logger;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('ecc_dealer_claims_status', 'id');
    }

    /**
     * Insert Claim status data to table
     * @param $rows
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveClaimStatusData($rows, $erp = null)
    {
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $where = [];
            if (!is_null($erp)) {
                $where = [
                    'erp_account_number = ?' => $erp
                ];
            }
            $connection->delete($this->getMainTable(), $where);
            if (!empty($rows)) {
                $connection->insertMultiple($this->getMainTable(), $rows);
            }
            $connection->commit();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->_logger->critical($e);
        }
    }

    /**
     * @param $erpAccountNumber
     * @param array $status
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getClaimsData($erpAccountNumber, $status = [])
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(['main_table' => $this->getMainTable()]);
        $select->where('erp_account_number = ?', $erpAccountNumber);
        if (!empty($status)) {
            $select->where('status_code IN (?)', $status);
        }
        return $connection->fetchAll($select);
    }


    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function clearData()
    {
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $connection->delete($this->getMainTable());
            $connection->commit();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->_logger->critical($e);
        }
        return;
    }
}
