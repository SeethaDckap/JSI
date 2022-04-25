<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elements\Setup\Patch\Data;

use Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\CollectionFactory as ArOrderCollection;
use Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\Payment\Collection;
use Epicor\Elements\Model\ResourceModel\Transaction;
use Epicor\Elements\Model\ResourceModel\Transaction\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateElementsTransactions
 * @package Epicor\Elements\Setup\Patch\Data
 */
class UpdateElementsTransactions implements DataPatchInterface
{
    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Transaction
     */
    private $resourceTransaction;

    /**
     * @var CollectionFactory
     */
    private $collection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Collection
     */
    private $arPayCollection;

    /**
     * @var ArOrderCollection
     */
    private $arOrderCollection;

    /**
     * UpdateElementsTransactions constructor.
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param Transaction $resourceTransaction
     * @param CollectionFactory $collection
     * @param Collection $arPayCollection
     * @param ArOrderCollection $arOrderCollection
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        OrderRepositoryInterface $orderRepository,
        Transaction $resourceTransaction,
        CollectionFactory $collection,
        Collection $arPayCollection,
        ArOrderCollection $arOrderCollection,
        LoggerInterface $logger
    ) {
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->orderRepository = $orderRepository;
        $this->resourceTransaction = $resourceTransaction;
        $this->collection = $collection;
        $this->logger = $logger;
        $this->arPayCollection = $arPayCollection;
        $this->arOrderCollection = $arOrderCollection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $data = $this->getTransactionIds();

        if (!empty($data)) {
            $orderIds = $this->getOrderIds($data);

            foreach ($orderIds as $key => $value) {
                $this->addData($data[$key], $value);
            }
        }

        $this->fillArData();
    }

    /**
     * @param int $key
     * @param int $value
     */
    public function addData($key, $value)
    {
        $transactionObj = $this->collection->create()
            ->addFieldToFilter('transaction_id', $key)
            ->getFirstItem();

        $transactionObj->setData('order_id', $value);

        try {
            $this->resourceTransaction->save($transactionObj);
        } catch (AlreadyExistsException $alreadyExistsException) {
            $this->logger->log($alreadyExistsException->getMessage());
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage());
        }
    }

    /**
     * Add Ar payment increment id for the corresponding transaction id
     */
    public function fillArData()
    {
        $arPayCol = $this->arPayCollection
            ->addFieldToFilter('method', ['like' => 'elements']);

        if ($arPayCol->count() <= 0) {
            return;
        }

        $arPayInfo = array();
        foreach ($arPayCol as $arPay) {
            $arPayInfo[$arPay->getParentId()] = $arPay->getLastTransId();
        }

        foreach ($arPayInfo as $k => $v) {
            $arCol = $this->arOrderCollection->create()
                ->addFieldToFilter('entity_id', $k)
                ->getFirstItem();

            $this->addData($arPayInfo[$arCol->getEntityId()], $arCol->getIncrementId());
        }
    }

    /**
     * @return array
     */
    private function getTransactionIds()
    {
        $filter = $this->filterBuilder
            ->setField('method')
            ->setConditionType('like')
            ->setValue('elements')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter]);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $items = $this->orderPaymentRepository->getList($searchCriteria)->getItems();

        $data = array();
        foreach ($items as $item) {
            $data[$item->getParentId()] = $item->getLastTransId();
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getOrderIds($data)
    {
        $filterOrders = $this->filterBuilder
            ->setField('entity_id')
            ->setConditionType('in')
            ->setValue(array_keys($data))
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filterOrders]);

        $searchCriteriaOrder = $this->searchCriteriaBuilder->create();
        $orders = $this->orderRepository->getList($searchCriteriaOrder)->getItems();

        $orderData = array();
        foreach ($orders as $order) {
            $orderData[$order->getEntityId()] = $order->getIncrementId();
        }

        return $orderData;
    }
}
