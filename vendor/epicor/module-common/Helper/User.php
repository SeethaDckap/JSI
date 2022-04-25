<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Helper;

/**
 * Class User
 * @package Epicor\Common\Helper
 */
class User extends \Epicor\Common\Helper\Data
{
    /**
     * Configuration path to customer password minimum length
     */
    const XML_PATH_MINIMUM_PASSWORD_LENGTH = 'customer/password/minimum_password_length';

    /**
     * Configuration path to customer password required character classes number
     */
    const XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER = 'customer/password/required_character_classes_number';

    /**
     * Minimum Password Length
     * @return int
     */
    public function getMinPasswordLength()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * Returns Required Character Classes Number
     * @return int
     */
    public function getRequiredClassNumber()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
    }

    /**
     * Returns the password strength meter html
     * @return string
     */
    public function getPasswordMeterHtml()
    {
        return "<div id=\"password-strength-meter-container\" data-role=\"password-strength-meter\" aria-live=\"polite\">
                        <div id=\"password-strength-meter\" class=\"password-strength-meter\">" .
            __('Password Strength:') .
            "<span id=\"password-strength-meter-label\" data-role=\"password-strength-meter-label\">" .
            __('No Password') .
            "</span>
                        </div>
                    </div>";
    }
}