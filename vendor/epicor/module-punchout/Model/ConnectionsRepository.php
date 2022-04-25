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

use Epicor\Punchout\Api\ConnectionsRepositoryInterface;
use Epicor\Punchout\Api\Data\ConnectionsInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Epicor\Punchout\Model\ResourceModel\Connections as ResourceConnection;
use Epicor\Punchout\Model\ResourceModel\ConnectionsFactory as ConnectionsResourceFactory;
use Epicor\Punchout\Model\ConnectionsFactory;

/**
 * Class GroupRepository
 *
 * @package Epicor\Punchout\Model
 */
class ConnectionsRepository implements ConnectionsRepositoryInterface
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
     * Connections factory.
     *
     * @var ConnectionsFactory
     */
    private $connectionsFactory;

    /**
     * Connections resource factory.
     *
     * @var ConnectionsResourceFactory
     */
    private $connectionResourceFactory;


    /**
     * ConnectionsRepository constructor.
     *
     * @param Http                       $request                   Http request.
     * @param ResourceConnection         $resource                  Resource connection.
     * @param ConnectionsFactory         $connectionsFactory        Connections fctory.
     * @param ConnectionsResourceFactory $connectionResourceFactory Connections resource factory.
     */
    public function __construct(
        Http $request,
        ResourceConnection $resource,
        ConnectionsFactory $connectionsFactory,
        ConnectionsResourceFactory $connectionResourceFactory
    ) {
        $this->request                   = $request;
        $this->resource                  = $resource;
        $this->connectionsFactory        = $connectionsFactory;
        $this->connectionResourceFactory = $connectionResourceFactory;

    }//end __construct()


    /**
     * Save groups data
     *
     * @param ConnectionsInterface|Connections $connection Connection Interface.
     *
     * @return Connections
     * @throws CouldNotSaveException Exception.
     */
    public function save(ConnectionsInterface $connection)
    {
        try {
            $this->resource->save($connection);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the connection: %1', $exception->getMessage()),
                $exception
            );
        }

        return $connection;

    }//end save()


    /**
     * @param integer $connectionId Connection ID.
     *
     * @return ConnectionsInterface|Connections
     * @throws NoSuchEntityException
     */
    public function getById($connectionId)
    {
        $connection = $this->connectionsFactory->create();
        if ($connectionId) {
            $this->connectionResourceFactory->create()->load($connection, $connectionId);
        }

        if (! $connection->getId()) {
            throw new NoSuchEntityException(
                __('The connection with the "%1" connectionId doesn\'t exist.', $connectionId)
            );
        }

        return $connection;
    }//end getById()


    /**
     * Delete connections
     *
     * @param ConnectionsInterface $connection Connections interface.
     *
     * @return boolean
     * @throws CouldNotDeleteException Exception.
     */
    public function delete(ConnectionsInterface $connection)
    {
        try {
            $this->resource->delete($connection);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Connection: %1', $exception->getMessage())
            );
        }

        return true;

    }//end delete()


    /**
     * Delete connection by given Identity
     *
     * @param string $connectionId Connection ID.
     *
     * @return boolean
     * @throws CouldNotDeleteException Exception.
     * @throws NoSuchEntityException Exception.
     */
    public function deleteById($connectionId)
    {
        return $this->delete($this->getById($connectionId));

    }//end deleteById()


    /**
     * Load Entity.
     *
     * @param string|null $id Connection ID.
     *
     * @return Connections
     * @throws NoSuchEntityException Exception.
     */
    public function loadEntity($id=null)
    {
        $connection = null;
        if (!$id) {
            $id = $this->request->getParam('entity_id', null);
        }

        if ($id != null) {
            $connection = $this->getById($id);
        } else {
            $connection = $this->connectionsFactory->create();
        }

        return $connection;

    }//end loadEntity()


}//end class

