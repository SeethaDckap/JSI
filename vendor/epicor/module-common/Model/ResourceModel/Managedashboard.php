<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;

/**
 * Admin rule resource model
 */
class Managedashboard extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Rules constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
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

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ecc_manage_dashboard', 'id');
    }

    public function getDashboardTable()
    {
        return $this->getConnection()->select()->from(['main_table' => $this->getMainTable()]);
    }

    public function loadActive($customer_id, $account_id)
    {
        $select = $this->getDashboardTable()->where('customer_id = '.$customer_id.' AND account_id = '.$account_id);
        return $select;
    }


    public function getDashboardConfiguration($customer_id, $accounttype = false, $account_id)
    {
        $connection = $this->getConnection();
        $select = $this->loadActive($customer_id, $account_id);
        if ($accounttype) {
            $select->where(
                'message_type IN ( ? )',
                $accounttype
            );
        }
        return $connection->fetchAll($select);
    }

    /**
     * Save ACL resources
     *
     * @param Customer Id
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveRel($customer_id, $account_id, $rows)
    {
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();

            $condition = ['customer_id = ?' => (int)$customer_id,'account_id = ?' => (int)$account_id];

            $connection->delete($this->getMainTable(), $condition);
            foreach ($rows as $row) {
                if ($rows) {
                    // If all was selected save it only and nothing else.
                    $insertData = $this->_prepareDataForTable(
                        new \Magento\Framework\DataObject($row),
                        $this->getMainTable()
                    );

                    $connection->insert($this->getMainTable(), $insertData);

                }
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
}
