<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\Attribute\EccBrand;

use Magento\Catalog\Model\Product\Action;
use Magento\Framework\Bulk\OperationManagementInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class Consumer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Action
     */
    private $productAction;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var OperationManagementInterface
     */
    private $operationManagement;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Consumer constructor.
     * @param OperationManagementInterface $operationManagement
     * @param Action $action
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param EntityManager $entityManager
     */
    public function __construct(
        OperationManagementInterface $operationManagement,
        Action $action,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        EntityManager $entityManager
    ) {
        $this->productAction = $action;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->operationManagement = $operationManagement;
        $this->entityManager = $entityManager;
    }

    /**
     * Process
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterface $operation
     * @throws \Exception
     *
     * @return void
     */
    public function process(\Magento\AsynchronousOperations\Api\Data\OperationInterface $operation)
    {
        try {
            $serializedData = $operation->getSerializedData();
            $data = $this->serializer->unserialize($serializedData);
            $this->execute($data);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = $e->getMessage();
        }

        $operation->setStatus($status ?? OperationInterface::STATUS_TYPE_COMPLETE)
            ->setErrorCode($errorCode ?? null)
            ->setResultMessage($message ?? null);

        $this->entityManager->save($operation);
    }

    /**
     * Execute
     *
     * @param array $data
     *
     * @return void
     */
    private function execute($data)
    {
        $info = $data['product_ids'];
        foreach ($info as $value => $ids) {
            $this->productAction->updateAttributes($ids, array('ecc_brand_updated' => $value), 0);
        }
    }
}
