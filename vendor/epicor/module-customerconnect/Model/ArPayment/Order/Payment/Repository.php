<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\ArPayment\Order\Payment;

use Magento\Framework\Exception\NoSuchEntityException;
use Epicor\Customerconnect\Api\OrderPaymentRepositoryInterface;
use Epicor\Customerconnect\Model\ArPayment\ResourceModel\Metadata;

/**
 * Class Repository
 */
class Repository implements OrderPaymentRepositoryInterface
{
    /**
     * Epicor\Customerconnect\Model\ArPayment\Order\Payment\Transaction[]
     *
     * @var array
     */
    private $registry = [];

    /**
     * @var Metadata
     */
    protected $metaData;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * @param Metadata $metaData
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        Metadata $metaData
    ) {
        $this->metaData = $metaData;
    }

   

    /**
     * Loads a specified order payment.
     *
     * @param int $id The order payment ID.
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function get($id)
    {
        if (!$id) {
            throw new \Magento\Framework\Exception\InputException(__('ID required'));
        }
        if (!isset($this->registry[$id])) {
            $entity = $this->metaData->getNewInstance()->load($id);
            if (!$entity->getId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }
            $this->registry[$id] = $entity;
        }
        return $this->registry[$id];
    }

    /**
     * Deletes a specified order payment.
     *
     * @return bool
     */
    public function delete(\Epicor\Customerconnect\Api\Data\OrderPaymentInterface $entity)
    {
        $this->metaData->getMapper()->delete($entity);
        return true;
    }

    /**
     * Performs persist operations for a specified order payment.
     *
     */
    public function save(\Epicor\Customerconnect\Api\Data\OrderPaymentInterface $entity)
    {
        $this->metaData->getMapper()->save($entity);
        return $entity;
    }

    /**
     * Creates new Order Payment instance.
     *
     */
    public function create()
    {
        return $this->metaData->getNewInstance();
    }
}
