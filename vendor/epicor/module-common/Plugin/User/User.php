<?php
/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Plugin\User;

/**
 * Class User
 * @package Epicor\Common\Plugin\User
 */
class User
{
    /**
     * @var \Epicor\B2b\Model\ResourceModel\User
     */
    private $user;

    /**
     * User constructor.
     * @param \Epicor\B2b\Model\ResourceModel\User $user
     */
    public function __construct(
        \Epicor\B2b\Model\ResourceModel\User $user
    ) {
        $this->user = $user;
    }

    /**
     * Check if the password is simple
     * @param \Magento\User\Model\User $subject
     * @param $result
     * @return array
     */
    public function afterValidate(
        \Magento\User\Model\User $subject,
        $result
    ) {
        if ($result) {
            $password = $subject->getPassword();
            $simplePassword = $this->user->getWeakPasswords($password);
            if (empty($simplePassword) === false) {
                $errorMessage = __('Sorry, but this password is weak. Please create another.');
                return [$errorMessage];
            }
        }
        return $result;
    }
}