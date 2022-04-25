<?php
namespace Silk\CustomAccount\Setup;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $quote_table = $connection->newTable(
            $installer->getTable('custom_quote')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [ 'identity' => true,
              'nullable' => false,
              'primary' => true,
              'unsigned' => true,
            ],
            'Entity ID'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [ 'nullable' => false,
            ],
            'Custom Id'
        )->addColumn(
            'data',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [ 'nullable' => true,
            ],
            'Data'
        )->addColumn(
            'create_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [ 'default'  => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
            ],
            'Create At'
        )->addColumn(
            'update_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [ 'default'  => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
            ],
            'Update At'
        );

        $connection->createTable($quote_table);

        $replace_table = $connection->newTable(
            $installer->getTable('custom_replace')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [ 'identity' => true,
              'nullable' => false,
              'primary' => true,
              'unsigned' => true,
            ],
            'Entity ID'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [ 'nullable' => false,
            ],
            'Custom Id'
        )->addColumn(
            'data',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [ 'nullable' => true,
            ],
            'Data'
        );

        $connection->createTable($replace_table);

        $installer->endSetup();
    }
}
