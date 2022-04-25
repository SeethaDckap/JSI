<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddEccBrand
 * @package Epicor\Database\Setup\Patch\Data
 */
class AddEccBrandOptions implements DataPatchInterface
{
    /**
     * ModuleDataSetupInterface
     *
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * EavSetupFactory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * AddEccBrand constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
           AddEccBrand::class
        ];
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
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        if (!$eavSetup->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'ecc_brand')) {
            return;
        }

        $attributeId = $eavSetup->getAttributeId(ProductAttributeInterface::ENTITY_TYPE_CODE, 'ecc_brand_updated');

        $options = [
            'values' => $this->getEccBrandOptions(
                $eavSetup->getAttributeId(ProductAttributeInterface::ENTITY_TYPE_CODE, 'ecc_brand')
            ),
            'attribute_id' => $attributeId,
        ];

        $eavSetup->addAttributeOption($options);
    }

    /**
     * @param $id
     * @return array
     */
    private function getEccBrandOptions($id)
    {
        $connection = $this->resourceConnection->getConnection();

        $tableName = $this->resourceConnection->getTableName('catalog_product_entity_varchar');
        $column = ['value'];

        $sqlQuery = $connection->select()
            ->from($tableName, $column)
            ->where('attribute_id = ?', $id);
        $result = $connection->fetchAll($sqlQuery);

        $options = [];
        if ($result) {
            foreach ($result as $r) {
                $val = $r['value'];
                if (($val != '') && (!is_null($val))) {
                    array_push($options, $val);
                }
            }
        }

        return array_unique($options);
    }
}
