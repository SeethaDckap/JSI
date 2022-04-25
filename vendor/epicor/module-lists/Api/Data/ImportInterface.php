<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Api\Data;

/**
 * Lists Mass Import interface.
 * @api
 */
interface ImportInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID         = 'id';
    const FILE_NAME  = 'file_name';
    const STATUS     = 'status';
    const MESSAGES   = 'messages';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get Group Id.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get File Name.
     *
     * @return string
     */
    public function getFileName();

    /**
     * Get status.
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * Get message.
     *
     * @return string|null
     */
    public function getMessage();

    /**
     * Get Meta Description.
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get Meta Description.
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set Group Id.
     *
     * @param int $groupId
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setId($groupId);

    /**
     * Set Name.
     *
     * @param string $name
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setFileName($name);

    /**
     * Set Status.
     *
     * @param string $status
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setStatus($status);

    /**
     * Set Message.
     *
     * @param string $message
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setMessage($message);

    /**
     * Set Created At.
     *
     * @param string $createdAt
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set Updated At.
     *
     * @param string $updatedAt
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setUpdatedAt($updatedAt);
}
