<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model;

/**
 * Class AbstractContext
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
abstract class AbstractContext
{
    /**
     * Implements JSON serialization for a class.
     * @return mixed
     */
    public function jsonSerialize()
    {
        return Utils::removeEmptyValues($this->data);
    }
}
