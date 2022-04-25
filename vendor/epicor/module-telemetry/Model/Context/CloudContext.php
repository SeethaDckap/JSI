<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\Context;

use Epicor\Telemetry\Model\AbstractContext;

/**
 * Class CloudContext. Data contract class for type Cloud.
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class CloudContext extends AbstractContext
{
    /**
     * Data array that will store all the values.
     * @var array
     */
    protected $data;

    /**
     * CloudContext constructor. Creates a new Cloud.
     */
    public function __construct()
    {
        $this->data = [];
    }

    /**
     * Gets the role field. Name of the role the application is a part of. Maps directly to the role name in azure.
     * @return mixed|void
     */
    public function getRole()
    {
        if (array_key_exists('ai.cloud.role', $this->data)) {
            return $this->data['ai.cloud.role'];
        }
        return;
    }

    /**
     * Sets the role field. Name of the role the application is a part of. Maps directly to the role name in azure.
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->data['ai.cloud.role'] = $role;
    }

    /**
     * Gets the roleInstance field.
     * Name of the instance where the application is running. Computer name for on-premisis, instance name for Azure.
     *  @return mixed|void
     */
    public function getRoleInstance()
    {
        if (array_key_exists('ai.cloud.roleInstance', $this->data)) {
            return $this->data['ai.cloud.roleInstance'];
        }
        return;
    }

    /**
     * Sets the roleInstance field.
     * Name of the instance where the application is running. Computer name for on-premisis, instance name for Azure.
     * @param mixed $roleInstance
     */
    public function setRoleInstance($roleInstance)
    {
        $this->data['ai.cloud.roleInstance'] = $roleInstance;
    }
}
