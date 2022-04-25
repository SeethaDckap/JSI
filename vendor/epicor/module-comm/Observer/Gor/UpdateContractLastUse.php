<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer\Gor;

class UpdateContractLastUse extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $contractCodes = array();
        $event = $observer->getEvent();
        $message = $event->getMessage();
        $order = $message->getOrder();
        //get order object

        $helper = $this->commMessagingHelper;
        $erpAccountId = $order->getEccErpAccountId() ?: $order->getCustomerGroupId();
        $erpAccount = $helper->getErpAccountInfo($erpAccountId);
        if ($erpAccount !== false) {
            $accountNumber = $erpAccount->getAccountNumber();
            $shortCode = $erpAccount->getShortCode();
            $delimiter = $this->commonHelper->getUomSeparator();
            //get ERP account number.

            if ($event->getResult()) {
                $writeConnection = $this->resourceConnection->getConnection();
                /* @var $writeConnection \Magento\Framework\DB\Adapter\AdapterInterface */

                if ($order->getEccContractCode()) {
                    array_push($contractCodes, $writeConnection->quote($accountNumber . $delimiter . $order->getEccContractCode()));
                    array_push($contractCodes, $writeConnection->quote($shortCode . $delimiter . $order->getEccContractCode()));
                }

                foreach ($order->getAllItems() as $order_item) {
                    if ($order_item->getEccContractCode()) {
                        array_push($contractCodes, $writeConnection->quote($accountNumber . $delimiter . $order_item->getEccContractCode()));
                        array_push($contractCodes, $writeConnection->quote($shortCode . $delimiter . $order_item->getEccContractCode()));
                    }
                }

                if (count($contractCodes) > 0) {
                    $contractCodes = array_unique($contractCodes);
                    $erpCode = implode(',', $contractCodes);

                    /** @var  \Magento\Framework\App\ResourceConnection $resource */
                    $resource = $this->resourceConnection;

                    $listsTable = $resource->getTableName('ecc_list');
                    $contractsTable = $resource->getTableName('ecc_contract');

                    $listQuery = 'SELECT id FROM ' . $listsTable . ' WHERE erp_code IN (' . $erpCode . ')';
                    $query = 'UPDATE ' . $contractsTable . ' SET last_used_time = NOW() WHERE list_id IN (' . $listQuery . ')';
                    $writeConnection->query($query);
                }
            }
        }
    }

}
