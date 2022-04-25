<?php
/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\B2b\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class User
 * @package Epicor\B2b\Model\ResourceModel
 */
class User extends AbstractDb
{

    /**
     * Define Main Table
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ecc_customer_passwords', 'password_id');
    }

    /**
     * Purge and get remaining old password hashes
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param int $retainLimit
     * @return array
     */
    public function getOldPasswords($customer, $retainLimit = 4)
    {
        $customerId = (int)$customer->getId();
        $table = $this->getMainTable();

        // purge expired passwords, except those which should be retained
        $retainPasswordIds = $this->getConnection()->fetchCol(
            $this->getConnection()
                ->select()
                ->from($table, 'password_id')
                ->where('customer_id = :customer_id')
                ->order('password_id ' . \Magento\Framework\DB\Select::SQL_DESC)
                ->limit($retainLimit),
            [':customer_id' => $customerId]
        );
        $where = [
            'customer_id = ?' => $customerId
        ];
        if ($retainPasswordIds) {
            $where['password_id NOT IN (?)'] = $retainPasswordIds;
        }
        $this->getConnection()->delete($table, $where);

        // get all remaining passwords
        return $this->getConnection()->fetchCol(
            $this->getConnection()
                ->select()
                ->from($table, 'password_hash')
                ->where('customer_id = :customer_id'),
            [':customer_id' => $customerId]
        );
    }

    /**
     * Remember a password hash for further usage
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $passwordHash
     * @return void
     */
    public function trackPassword($customer, $passwordHash)
    {
        $this->getConnection()->insert(
            $this->getMainTable(),
            [
                'customer_id' => $customer->getId(),
                'password_hash' => $passwordHash
            ]
        );
    }

    /**
     * Returns Weak passwords
     * @param string $password
     * @return array
     */
    public function getWeakPasswords($password)
    {
        $table = $this->getTable('ecc_weak_passwords_dictionary');
        return $this->getConnection()->fetchCol(
            $this->getConnection()
                ->select()
                ->from($table, 'passwords')
                ->where('passwords = :passwords'),
            [':passwords' => $password]
        );
    }
}