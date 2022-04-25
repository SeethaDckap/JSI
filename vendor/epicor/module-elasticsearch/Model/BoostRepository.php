<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Epicor\Elasticsearch\Api\BoostRepositoryInterface;
use Epicor\Elasticsearch\Model\ResourceModel\Boost\Collection as BoostCollection;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Boost Repository Object
 *
 */
class BoostRepository implements BoostRepositoryInterface
{
    /**
     * Boost Factory
     *
     * @var BoostFactory
     */
    private $boostFactory;

    /**
     * repository cache for boost, by ids
     *
     * @var \Epicor\Elasticsearch\Api\Data\BoostInterface[]
     */
    private $boostRepositoryById = [];

    /**
     * Boost Collection Factory
     *
     * @var boostCollection
     */
    private $boostCollectionFactory;

    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    private $entityManager;

    /**
     * PHP Constructor
     *
     * @param BoostFactory    $boostFactory
     * @param BoostCollection $boostCollectionFactory
     * @param EntityManager $entityManager
     */
    public function __construct(
        BoostFactory $boostFactory,
        BoostCollection $boostCollectionFactory,
        EntityManager $entityManager
    ) {
        $this->boostFactory = $boostFactory;
        $this->boostCollectionFactory = $boostCollectionFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * Retrieve boost by its ID
     *
     * @param int $boostId Id of the boost.
     *
     * @return \Epicor\Elasticsearch\Api\Data\BoostInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($boostId)
    {
        if (!isset($this->boostRepositoryById[$boostId])) {
            $boostModel = $this->boostFactory->create();
            $boost = $this->entityManager->load($boostModel, $boostId);
            if (!$boost->getId()) {
                throw NoSuchEntityException::singleField('boostId', $boost);
            }

            $this->boostRepositoryById[$boostId] = $boost;
        }

        return $this->boostRepositoryById[$boostId];
    }

    /**
     * Retrieve list of boost
     *
     * @return \Epicor\Elasticsearch\Api\Data\BoostSearchResultsInterface
     */
    public function getList()
    {
        $collection = $this->boostCollectionFactory->create();
        $boosters = $collection->getItems();

        return $boosters;
    }

    /**
     * save a boost
     *
     * @param \Epicor\Elasticsearch\Api\Data\BoostInterface $boost
     *
     * @return \Epicor\Elasticsearch\Api\Data\BoostInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Epicor\Elasticsearch\Api\Data\BoostInterface $boost)
    {
        try {
            $this->entityManager->save($boost);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the boost: %1',
                $exception->getMessage()
            ));
        }

        $this->boostRepositoryById[$boost->getId()] = $boost;

        return $boost;
    }

    /**
     * Delete Boost
     *
     * @param \Epicor\Elasticsearch\Api\Data\BoostInterface $boost
     * @return \Epicor\Elasticsearch\Api\Data\BoostInterface
     * @throws \LogicException
     * @throws \Exception
     */
    public function delete(\Epicor\Elasticsearch\Api\Data\BoostInterface $boost)
    {
        $boostId = $boost->getId();

        $this->entityManager->delete($boost);

        if (isset($this->boostRepositoryById[$boostId])) {
            unset($this->boostRepositoryById[$boostId]);
        }

        return $boost;
    }

    /**
     * Remove boost by given ID
     *
     * @param int $boostId
     *
     * @return \Epicor\Elasticsearch\Api\Data\BoostInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($boostId)
    {
        return $this->delete($this->getById($boostId));
    }
}
