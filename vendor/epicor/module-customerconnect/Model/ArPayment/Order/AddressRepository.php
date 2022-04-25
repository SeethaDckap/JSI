<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\Order;

use Epicor\Customerconnect\Model\ArPayment\ResourceModel\Metadata;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;

/**
 * Repository class for @see \Epicor\Customerconnect\Api\Data\OrderAddressInterface
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepository implements \Epicor\Customerconnect\Api\OrderAddressRepositoryInterface
{
    /**
     * @var Metadata
     */
    protected $metadata;


    /**
     * @var \Epicor\Customerconnect\Api\Data\OrderAddressInterface[]
     */
    protected $registry = [];
    
    
    /**
     * AddressRepository constructor.
     * @param Metadata $metadata
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        Metadata $metadata
    ) {
        $this->metadata = $metadata;
    }

    /**
     * Loads a specified order address.
     *
     * @param int $id
     * @return \Epicor\Customerconnect\Api\Data\OrderAddressInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('Id required'));
        }

        if (!isset($this->registry[$id])) {
            /** @var \Epicor\Customerconnect\Api\Data\OrderAddressInterface $entity */
            $entity = $this->metadata->getNewInstance()->load($id);
            if (!$entity->getEntityId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }

            $this->registry[$id] = $entity;
        }

        return $this->registry[$id];
    }


    /**
     * Deletes a specified order address.
     *
     * @param \Epicor\Customerconnect\Api\Data\OrderAddressInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Epicor\Customerconnect\Api\Data\OrderAddressInterface $entity)
    {
        try {
            $this->metadata->getMapper()->delete($entity);

            unset($this->registry[$entity->getEntityId()]);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete order address'), $e);
        }

        return true;
    }

    /**
     * Deletes order address by Id.
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id)
    {
        $entity = $this->get($id);

        return $this->delete($entity);
    }

    /**
     * Performs persist operations for a specified order address.
     *
     * @param \Epicor\Customerconnect\Api\Data\OrderAddressInterface $entity
     * @return \Epicor\Customerconnect\Api\Data\OrderAddressInterface
     * @throws CouldNotSaveException
     */
    public function save(\Epicor\Customerconnect\Api\Data\OrderAddressInterface $entity)
    {
        try {
            $this->metadata->getMapper()->save($entity);
            $this->registry[$entity->getEntityId()] = $entity;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save order address'), $e);
        }

        return $this->registry[$entity->getEntityId()];
    }

    /**
     * Creates new order address instance.
     *
     * @return \Epicor\Customerconnect\Api\Data\OrderAddressInterface
     */
    public function create()
    {
        return $this->metadata->getNewInstance();
    }

}
