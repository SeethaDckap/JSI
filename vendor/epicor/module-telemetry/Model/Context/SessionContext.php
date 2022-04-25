<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\Context;

use Epicor\Telemetry\Model\AbstractContext;

/**
 * Class SessionContext
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class SessionContext extends AbstractContext
{
    /**
     * Data array that will store all the values.
     * @var array
     */
    protected $data;

    /**
     * SessionContext constructor.
     */
    public function __construct()
    {
        $this->data = [];
    }

    /**
     * Session Id of the user
     * @return mixed|void
     */
    public function getId()
    {
        if (array_key_exists('ai.session.id', $this->data)) {
            return $this->data['ai.session.id'];
        }
        return;
    }

    /**
     * Setting the Session Id of the user
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->data['ai.session.id'] = $id;
    }

    /**
     * @return bool
     */
    public function getIsFirst()
    {
        if (array_key_exists('ai.session.isFirst', $this->data)) {
            return $this->data['ai.session.isFirst'];
        }
        return;
    }

    /**
     * @param mixed $isFirst
     */
    public function setIsFirst($isFirst)
    {
        $this->data['ai.session.isFirst'] = var_export($isFirst, TRUE);
    }

}