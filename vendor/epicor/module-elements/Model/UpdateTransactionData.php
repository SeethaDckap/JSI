<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elements\Model;

use Epicor\Elements\Model\ResourceModel\Transaction\Collection;
use Magento\Framework\Exception\AlreadyExistsException;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateTransactionData
 * @package Epicor\Elements\Model
 */
class UpdateTransactionData
{
    /**
     * @var ResourceModel\Transaction
     */
    private $resourceTransaction;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpdateTransactionData constructor.
     * @param ResourceModel\Transaction $resourceTransaction
     * @param Collection $collection
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceModel\Transaction $resourceTransaction,
        Collection $collection,
        LoggerInterface $logger
    ) {
        $this->resourceTransaction = $resourceTransaction;
        $this->collection = $collection;
        $this->logger = $logger;
    }

    /**
     * @param string $transactionId
     * @param string $orderId
     */
    public function updateElementsTransaction($transactionId, $orderId)
    {
        $transactionObj = $this->collection
            ->addFieldToFilter('transaction_id', $transactionId)
            ->getFirstItem();

        $transactionObj->setData('order_id', $orderId);

        try {
            $this->resourceTransaction->save($transactionObj);
        } catch (AlreadyExistsException $alreadyExistsException) {
            $this->logger->log($alreadyExistsException->getMessage());
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage());
        }
    }
}
