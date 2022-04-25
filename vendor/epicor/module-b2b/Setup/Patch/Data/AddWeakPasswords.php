<?php
/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\B2b\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class WeakPasswords
 * @package Epicor\B2b\Setup\Patch\Data
 */
class AddWeakPasswords implements DataPatchInterface, PatchVersionInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var string[]
     */
    private $weakPasswords = [
        '123456',
        '1234567890',
        '1234567',
        'PASSWORD',
        '123',
        'password',
        'test123',
        'aaaaaa',
        '123123123',
        'hello',
        'qwerty',
        'qwert',
        '123321',
        'pass123',
        'asdfghjkl',
        '12345',
        'abcd1234',
        '654321',
        'qwertyui',
        'asd123',
        '12345678',
        'password1',
        'qazwsx',
        'asdf1234',
        'asdasd',
        '1234',
        '12341234',
        'asdf',
        '87654321',
        '123654',
        '123456789',
        '123abc',
        'testing',
        '321321',
        'asdf123',
        'abc123',
        'qwer1234',
        'pass',
        'test1234',
        '1password',
        'test',
        '987654321',
        'asdfasdf',
        '1234abcd',
        '123123',
        'qwerty123',
        'testtest',
        '123456'
    ];

    /**
     * AddWeakPasswords constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Insert weak passwords into table
     * @return AddWeakPasswords|void
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $weakPasswordsTable = $this->moduleDataSetup->getTable('ecc_weak_passwords_dictionary');
        $data = $this->getWeakPasswords();
        $connection->insertArray($weakPasswordsTable, ['passwords'], $data);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * Returns the list of Weak Passwords
     * @return string[]
     */
    private function getWeakPasswords()
    {
        return $this->weakPasswords;
    }

}