<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Lists
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Model;

use Epicor\Lists\Api\ImportRepositoryInterface;
use Epicor\Lists\Api\Data\ImportInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Epicor\Lists\Model\ResourceModel\Import as ResourceConnection;
use Epicor\Lists\Model\ResourceModel\ImportFactory as ImportResourceFactory;
use Epicor\Lists\Model\ImportFactory;

/**
 * Class ImportRepository
 *
 * @package Epicor\Lists\Model
 */
class ImportRepository implements ImportRepositoryInterface
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
     * Import factory.
     *
     * @var ImportFactory
     */
    private $importFactory;

    /**
     * Import resource factory.
     *
     * @var ImportResourceFactory
     */
    private $importResourceFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;


    /**
     * ImportRepository constructor.
     *
     * @param Http                         $request               Http request.
     * @param ResourceConnection           $resource              Resource connection.
     * @param ImportFactory                $importFactory         Transaction logs fctory.
     * @param ImportResourceFactory        $importResourceFactory Import resource factory.
     * @param CollectionProcessorInterface $collectionProcessor   Collection Processor.
     */
    public function __construct(
        Http $request,
        ResourceConnection $resource,
        ImportFactory $importFactory,
        ImportResourceFactory $importResourceFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->request = $request;
        $this->resource = $resource;
        $this->importFactory = $importFactory;
        $this->importResourceFactory = $importResourceFactory;
        $this->collectionProcessor = $collectionProcessor;

    }//end __construct()


    /**
     * Save groups data
     *
     * @param ImportInterface|Import $import Log Interface.
     *
     * @return Import
     * @throws CouldNotSaveException Exception.
     */
    public function save(ImportInterface $import)
    {
        try {
            $this->resource->save($import);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the Log: %1', $exception->getMessage()),
                $exception
            );
        }

        return $import;

    }//end save()


    /**
     * @param integer $id Entity ID.
     *
     * @return ImportInterface|import
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $import = $this->importFactory->create();
        if ($id) {
            $this->importResourceFactory->create()->load($import, $id);
        }

        if (!$import->getId()) {
            throw new NoSuchEntityException(
                __('The Log with the "%1" Log Id doesn\'t exist.', $id)
            );
        }

        return $import;
    }//end getById()


    /**
     * Load Entity.
     *
     * @param string|null $id Log ID.
     *
     * @return Import
     * @throws NoSuchEntityException Exception.
     */
    public function loadEntity($id = null)
    {
        if (!$id) {
            $id = $this->request->getParam('id', null);
        }

        if ($id != null) {
            $import = $this->getById($id);
        } else {
            $import = $this->importFactory->create();
        }

        return $import;

    }//end loadEntity()

    /**
     * @param ImportInterface $import
     *
     * @return bool
     * @throws CouldNotSaveException
     */
    public function delete(ImportInterface $import)
    {
        try {
            $this->resource->delete($import);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not delete the ERP account budget: %1', $exception->getMessage()),
                $exception
            );
        }

        return true;
    }

}//end class

