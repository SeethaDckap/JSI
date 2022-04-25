<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\Context;

use Epicor\Telemetry\Model\AbstractContext;

/**
 * Class UserContext
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class UserContext extends AbstractContext
{
    /**
     * Data array that will store all the values.
     * @var array
     */
    protected $data;

    /**
     * UserContext constructor.
     */
    public function __construct()
    {
        $this->data = [];
    }

    /**
     * User Account Id
     * @return mixed|void
     */
    public function getAccountId()
    {
        if (array_key_exists('ai.user.accountId', $this->data)) {
            return $this->data['ai.user.accountId'];
        }
        return;
    }

    /**
     * Set User Account Id
     * @param mixed $accountId
     */
    public function setAccountId($accountId)
    {
        $this->data['ai.user.accountId'] = $accountId;
    }

    /**
     * Get User Id
     * @return mixed|void
     */
    public function getId()
    {
        if (array_key_exists('ai.user.id', $this->data)) {
            return $this->data['ai.user.id'];
        }
        return;
    }

    /**
     * Set User Id
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->data['ai.user.id'] = $id;
    }

    /**
     * Gets User Auth id
     * @return mixed|void
     */
    public function getAuthUserId()
    {
        if (array_key_exists('ai.user.authUserId', $this->data)) {
            return $this->data['ai.user.authUserId'];
        }
        return;
    }

    /**
     * Sets User Auth Id
     * @param mixed $authUserId
     */
    public function setAuthUserId($authUserId)
    {
        $this->data['ai.user.authUserId'] = $authUserId;
    }

}