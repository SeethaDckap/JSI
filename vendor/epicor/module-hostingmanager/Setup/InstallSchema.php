<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\HostingManager\Setup;


use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        //ecc_hosting_certificate
        $installer->getConnection()->dropTable($installer->getTable('ecc_hosting_certificate'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ecc_hosting_certificate')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID')
            ->addColumn(
                'name',
                Table::TYPE_TEXT, 255, ['nullable' => false],
                'Site Name')
            ->addColumn(
                'request',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Request')
            ->addColumn(
                'private_key',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Private Key')
            ->addColumn(
                'certificate',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Certificate')
            ->addColumn(
                'c_a_certificate',
                Table::TYPE_TEXT,
                '64k',
                [],
                'CA Certificate'
            )->addColumn(
                'issue_number',
                Table::TYPE_INTEGER,
                null,
                ['default' => 0],
                'Certificate Issue Number'
            );
        $installer->getConnection()->createTable($table);

        //install ecc_hosting_site
        $installer->getConnection()->dropTable($installer->getTable('ecc_hosting_site'));
        $table = $installer->getConnection()->newTable($installer->getTable('ecc_hosting_site'));
        $table->addColumn('entity_id', Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity ID');
        $table->addColumn('name', Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Site Name');
        $table->addColumn('url', Table::TYPE_TEXT, '64k', array(
            'nullable' => false
        ), 'Site Url');
        $table->addColumn('is_website', Table::TYPE_BOOLEAN, null, array(
            'default' => true,
        ), 'Is Website');
        $table->addColumn('code', Table::TYPE_TEXT, 32, array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Website/Store Code');
        $table->addColumn('child_id', Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Website/Store ID');
        $table->addColumn('certificate_id', Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => true,
        ), 'SSL Cert ID');

        $table->addColumn('is_default', Table::TYPE_BOOLEAN, null, array(
            'default' => 0
        ), 'Default Website Scope');
        $table->addColumn('secure', Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
            'default' => 0,
        ), 'All pages on your site run on https');
        $table->addColumn('extra_domains', Table::TYPE_TEXT, '64k', array(
            'nullable' => true,
        ), 'Extra domains');
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_hosting_site'),
                array('child_id')
            ),
            'child_id');
        $table->addIndex(
            $installer->getIdxName(
                $installer->getTable('ecc_hosting_site'),
                array('certificate_id')
            ),
            'certificate_id');

        $table->addForeignKey(
            $installer->getFkName(
                $installer->getTable('ecc_hosting_certificate'),
                'entity_id',
                $installer->getTable('ecc_hosting_site'),
                'certificate_id'),
            'certificate_id',
            $installer->getTable('ecc_hosting_certificate'), 'entity_id',
            Table::ACTION_SET_NULL,
            Table::ACTION_NO_ACTION);

        $installer->getConnection()->createTable($table);
        
        $installer->endSetup();
    }
}