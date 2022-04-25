<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model;

use Epicor\Punchout\Api\TransactionlogsRepositoryInterface;
use Epicor\Punchout\Api\Data\TransactionlogsInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Epicor\Punchout\Model\ResourceModel\Transactionlogs as ResourceConnection;
use Epicor\Punchout\Model\ResourceModel\TransactionlogsFactory as TransactionlogsResourceFactory;
use Epicor\Punchout\Model\TransactionlogsFactory;

/**
 * Class TransactionlogsRepository
 *
 * @package Epicor\Punchout\Model
 */
class TransactionlogsRepository implements TransactionlogsRepositoryInterface
{

    /**
     * Http request.
     *
     * @var Http
     */
    private $request;

    /**
     * Resource connection.
     *
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Transactionlogs factory.
     *
     * @var TransactionlogsFactory
     */
    private $transactionlogsFactory;

    /**
     * Transactionlogs resource factory.
     *
     * @var TransactionlogsResourceFactory
     */
    private $transactionlogsResourceFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;


    /**
     * TransactionlogsRepository constructor.
     *
     * @param Http $request Http request.
     * @param ResourceConnection $resource Resource connection.
     * @param TransactionlogsFactory $transactionlogsFactory Transaction logs fctory.
     * @param TransactionlogsResourceFactory $transactionlogsResourceFactory Transactionlogs resource factory.
     * @param CollectionProcessorInterface $collectionProcessor Collection Processor.
     */
    public function __construct(
        Http $request,
        ResourceConnection $resource,
        TransactionlogsFactory $transactionlogsFactory,
        TransactionlogsResourceFactory $transactionlogsResourceFactory,
        CollectionProcessorInterface $collectionProcessor
    )
    {
        $this->request = $request;
        $this->resource = $resource;
        $this->transactionlogsFactory = $transactionlogsFactory;
        $this->transactionlogsResourceFactory = $transactionlogsResourceFactory;
        $this->collectionProcessor = $collectionProcessor;

    }//end __construct()


    /**
     * Save groups data
     *
     * @param TransactionlogsInterface|Transactionlogs $transactionlogs Log Interface.
     *
     * @return Transactionlogs
     * @throws CouldNotSaveException Exception.
     */
    public function save(TransactionlogsInterface $transactionlogs)
    {
        try {
            $this->resource->save($transactionlogs);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the Log: %1', $exception->getMessage()),
                $exception
            );
        }

        return $transactionlogs;

    }//end save()


    /**
     * @param integer $id Entity ID.
     *
     * @return TransactionlogsInterface|transactionlogs
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $transactionlogs = $this->transactionlogsFactory->create();
        if ($id) {
            $this->transactionlogsResourceFactory->create()->load($transactionlogs, $id);
        }

        if (!$transactionlogs->getId()) {
            throw new NoSuchEntityException(
                __('The Log with the "%1" Log Id doesn\'t exist.', $id)
            );
        }

        return $transactionlogs;
    }//end getById()


    /**
     * Delete Transaction log
     *
     * @param TransactionlogsInterface $transactionlogs Transactionlogs interface.
     *
     * @return boolean
     * @throws CouldNotDeleteException Exception.
     */
    public function delete(TransactionlogsInterface $transactionlogs)
    {
        try {
            $this->resource->delete($transactionlogs);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Log: %1', $exception->getMessage())
            );
        }

        return true;

    }//end delete


    /**
     * Delete Transaction log by given Identity
     *
     * @param string $id ID.
     *
     * @return boolean
     * @throws CouldNotDeleteException Exception.
     * @throws NoSuchEntityException Exception.
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));

    }//end deleteById()


    /**
     * Load Transactionlogs data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return TransactionlogsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $collection = $this->blockCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var Data\BlockSearchResultsInterface $searchResults */
        $searchResults = $this->transactionlogsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }


    /**
     * Load Entity.
     *
     * @param string|null $id Log ID.
     *
     * @return Transactionlogs
     * @throws NoSuchEntityException Exception.
     */
    public function loadEntity($id = null)
    {
        if (!$id) {
            $id = $this->request->getParam('entity_id', null);
        }

        if ($id != null) {
            $transactionlog = $this->getById($id);
        } else {
            $transactionlog = $this->transactionlogsFactory->create();
        }

        return $transactionlog;

    }//end loadEntity()

}//end class

