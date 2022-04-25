<?php
/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\B2b\Plugin\Customer;

use Epicor\B2b\Model\ResourceModel\User;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;

/**
 * Class AccountManagement
 * @package Epicor\B2b\Plugin\Customer
 */
class AccountManagement
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var GetCustomerByToken
     */
    private $getByToken;

    /**
     * AccountManagement constructor.
     * @param User $user
     * @param EncryptorInterface $encryptor
     * @param CustomerRepositoryInterface $customerRepository
     * @param GetCustomerByToken $getByToken
     */
    public function __construct(
        User $user,
        EncryptorInterface $encryptor,
        CustomerRepositoryInterface $customerRepository,
        GetCustomerByToken $getByToken
    ) {
        $this->user = $user;
        $this->encryptor = $encryptor;
        $this->customerRepository = $customerRepository;
        $this->getByToken = $getByToken;
    }

    /**
     * Check if new password is previoulsy used and if its simple
     * @param \Magento\Customer\Model\AccountManagement $subject
     * @param \Closure $proceed
     * @param string $email
     * @param string $currentPassword
     * @param string $newPassword
     * @return false|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundChangePassword(
        \Magento\Customer\Model\AccountManagement $subject,
        \Closure $proceed,
        $email,
        $currentPassword,
        $newPassword
    ) {
        $customer = $this->customerRepository->get($email);
        $this->checkOldPasswords($customer, $newPassword);
        $this->checkForSimplePassword($newPassword);
        $result = $proceed($email, $currentPassword, $newPassword);
        $this->trackPassword($customer, $newPassword);
        return $result;
    }

    /**
     * Check if new password is previoulsy used and if its simple
     * @param \Magento\Customer\Model\AccountManagement $subject
     * @param \Closure $proceed
     * @param int $customerId
     * @param string $currentPassword
     * @param string $newPassword
     * @return false|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundChangePasswordById(
        \Magento\Customer\Model\AccountManagement $subject,
        \Closure $proceed,
        $customerId,
        $currentPassword,
        $newPassword
    ) {
        $customer = $this->customerRepository->getById($customerId);
        $this->checkOldPasswords($customer, $newPassword);
        $this->checkForSimplePassword($newPassword);
        $result = $proceed($customerId, $currentPassword, $newPassword);
        $this->trackPassword($customer, $newPassword);
        return $result;
    }

    /**
     * Check if new password is previoulsy used and if its simple
     * @param \Magento\Customer\Model\AccountManagement $subject
     * @param \Closure $proceed
     * @param int $customerId
     * @param string $currentPassword
     * @param string $newPassword
     * @return false|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundResetPassword(
        \Magento\Customer\Model\AccountManagement $subject,
        \Closure $proceed,
        $email,
        $resetToken,
        $newPassword
    ) {
        if (!$email) {
            $customer = $this->getByToken->execute($resetToken);
        } else {
            $customer = $this->customerRepository->get($email);
        }
        $this->checkOldPasswords($customer, $newPassword);
        $this->checkForSimplePassword($newPassword);
        $result = $proceed($email, $resetToken, $newPassword);
        $this->trackPassword($customer, $newPassword);
        return $result;
    }

    /**
     * Check if new password is previoulsy used and if its simple
     * @param \Magento\Customer\Model\AccountManagement $subject
     * @param \Closure $proceed
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param null|string $password
     * @param string $redirectUrl
     * @return mixed
     * @throws InputException
     */
    public function aroundCreateAccount(
        \Magento\Customer\Model\AccountManagement $subject,
        \Closure $proceed,
        $customer,
        $password = null,
        $redirectUrl = ''
    ) {
        if ($password !== null) {
            $this->checkForSimplePassword($password);
        }
        $result = $proceed($customer, $password, $redirectUrl);
        $this->trackPassword($result, $password);
        return $result;
    }

    /**
     * Create a hash for the given password
     * @param string $password
     * @return string
     */
    private function createPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * Check if its previously used password
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $newPassword
     * @return bool
     * @throws InputException
     */
    private function checkOldPasswords($customer, $newPassword)
    {
        $oldPasswordHash = $this->user->getOldPasswords($customer);
        if (!empty($oldPasswordHash)) {
            foreach ($oldPasswordHash as $hash) {
                if ($this->encryptor->isValidHash($newPassword, $hash)) {
                    throw new InputException(
                        __(
                            'Sorry, but this password has already been used. Please create another.'
                        )
                    );
                }
            }
        }
        return true;
    }

    /**
     * Check if the password is from weak password dictionary
     * @param string $password
     * @return bool
     */
    private function checkForSimplePassword($password)
    {
        $simplePassList = $this->user->getWeakPasswords($password);
        if (empty($simplePassList) === false) {
            throw new InputException(
                __(
                    'Sorry, but this password is weak. Please create another.'
                )
            );
        }
        return true;
    }

    /**
     * Remember a password hash for further usage
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $passwordHash
     */
    private function trackPassword($customer, $password)
    {
        $newPasswordHash = $this->createPasswordHash($password);
        $this->user->trackPassword($customer, $newPasswordHash);
    }
}