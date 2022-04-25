<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Epicor\Customerconnect\Api\Data\OrderInterface;
use Epicor\Customerconnect\Model\ArPayment\ResourceModel\Metadata;

/**
 * Repository class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ArpaymentsRepository implements \Epicor\Customerconnect\Api\OrderRepositoryInterface
{
    /**
     * @var Metadata
     */
    protected $metadata;
    
    /**
     * @var OrderInterface[]
     */
    protected $registry = [];

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * Constructor
     *
     * @param Metadata $metadata
     * @param SearchResultFactory $searchResultFactory
     * @param \Magento\Sales\Api\Data\OrderExtensionFactory|null $orderExtensionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        Metadata $metadata,
        JoinProcessorInterface $extensionAttributesJoinProcessor = null
    ) {
        $this->metadata = $metadata;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor
            ?: ObjectManager::getInstance()->get(JoinProcessorInterface::class);
    }

    /**
     * load entity
     *
     * @param int $id
     * @return \Epicor\Customerconnect\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('Id required'));
        }
        if (!isset($this->registry[$id])) {
            /** @var OrderInterface $entity */
            $entity = $this->metadata->getNewInstance()->load($id);
            if (!$entity->getEntityId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }
            $this->registry[$id] = $entity;
        }
        return $this->registry[$id];
    }

    /**
     * Register entity to delete
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $entity
     * @return bool
     */
    public function delete(\Epicor\Customerconnect\Api\Data\OrderInterface $entity)
    {
        $this->metadata->getMapper()->delete($entity);
        unset($this->registry[$entity->getEntityId()]);
        return true;
    }

    /**
     * Delete entity by Id
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
     * Perform persist operations for one entity
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $entity
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function save(\Epicor\Customerconnect\Api\Data\OrderInterface $entity)
    {
        $this->metadata->getMapper()->save($entity);
        $this->registry[$entity->getEntityId()] = $entity;
        return $this->registry[$entity->getEntityId()];
    }
}
