<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Api
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Punchout\Api;

use Epicor\Punchout\Api\Data\ConnectionsInterface;
use Epicor\Punchout\Model\Connections;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface ConnectionRepositoryInterface
 *
 * @package Epicor\Punchout\Api
 */
interface ConnectionsRepositoryInterface
{


    /**
     * Save connections.
     *
     * @param ConnectionsInterface $connection Connection interface.
     *
     * @return ConnectionsInterface
     * @throws LocalizedException Exception.
     */
    public function save(ConnectionsInterface $connection);


    /**
     * Retrieve connection.
     *
     * @param integer $connectionId Connection ID.
     *
     * @return ConnectionsInterface
     * @throws LocalizedException Exception.
     */
    public function getById($connectionId);


    /**
     * Delete Connection.
     *
     * @param ConnectionsInterface $connection Connection interface.
     *
     * @return boolean true on success
     * @throws LocalizedException Exception.
     */
    public function delete(ConnectionsInterface $connection);


    /**
     * Delete connection by ID.
     *
     * @param integer $connectionId Connection ID.
     *
     * @return boolean true on success
     * @throws NoSuchEntityException Exception.
     * @throws LocalizedException Exception.
     */
    public function deleteById($connectionId);


    /**
     * Load Entity.
     *
     * @param string|null $id Connection ID.
     *
     * @return Connections
     */
    public function loadEntity($id=null);


}
