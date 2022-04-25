<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Serialize\Serializer;

/**
 * Validate JSON string
 */
class JsonValidator
{
    /**
     * Check if string is valid JSON string
     *
     * @param string $string
     * @return bool
     */
    public function isValid($string)
    {
        if ($string !== false && $string !== null && $string !== '') {
            json_decode($string);
            if (json_last_error() === JSON_ERROR_NONE) {
                return true;
            }
        }
        return false;
    }
}
