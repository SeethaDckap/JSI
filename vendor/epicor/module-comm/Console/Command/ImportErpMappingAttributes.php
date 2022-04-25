<?php
/**
 * Copyright © 2019-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Console\Command;

use Magento\Framework\App\ResourceConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This CLI is to rebuilt lost ERP attribute mapping data,
 * caused due to bug reported in WSO-8526. This CLI will be
 * deprecated in future releases.
 *
 * Class ImportErpMappingAttributes
 * @package Epicor\Comm\Console\Command
 * @deprecated
 */
class ImportErpMappingAttributes extends Command
{
    /* @var $resource ResourceConnection */
    private $resource;

    /**
     * ImportErpMappingAttributes constructor.
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        parent::__construct();
        $this->resource = $resource;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('comm:import-erp-mapping-attributes');
        $this->setDescription(
            'Rebuild ERP attribute mapping configurations from STK created product attributes'
        );

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */
        $writeConnection = $this->resource->getConnection('core_write');

        try
        {
            $writeConnection->startSetup();
            $writeConnection->beginTransaction();
            $foundData = false;
            $sql = "select 
                eav.attribute_code as attribute_code, 
                eav.frontend_input as input_type, 
                c_eav.is_searchable as is_searchable,
                c_eav.search_weight as search_weight,
                c_eav.is_visible_in_advanced_search as is_visible_in_advanced_search,
                c_eav.is_comparable as is_comparable,
                c_eav.is_filterable as is_filterable,
                c_eav.is_filterable_in_search as is_filterable_in_search,
                c_eav.position as position,
                c_eav.is_used_for_promo_rules as is_used_for_promo_rules,
                c_eav.is_html_allowed_on_front as is_html_allowed_on_front,
                c_eav.is_visible_on_front as is_visible_on_front,
                c_eav.used_in_product_listing as used_in_product_listing,
                c_eav.used_for_sort_by as used_for_sort_by
            from eav_attribute as eav 
            left join catalog_eav_attribute as c_eav 
            on eav.attribute_id = c_eav.attribute_id 
            where eav.ecc_created_by = 'STK'";
            $result = $writeConnection->fetchAll($sql);

            $attributesToImport = array();

            if (count($result) > 0) {
                foreach ($result as $erpAttribute) {
                    $sqlForExist = "select * 
                                    from ecc_erp_mapping_attributes 
                                    where attribute_code='" . $erpAttribute['attribute_code'] . "'";
                    $foundAttribute = $writeConnection->fetchAll($sqlForExist);
                    if (count($foundAttribute) == 0) {
                        $foundData = true;
                        $attributesToImport[] = $erpAttribute;
                    }
                }
                if (count($attributesToImport) > 0) {
                    $writeConnection->insertMultiple(
                        'ecc_erp_mapping_attributes',
                        $attributesToImport
                    );
                }
            }
            $writeConnection->commit();
            $writeConnection->endSetup();
            if ($foundData) {
                $output->writeln("ECC ERP Mapping Attributes Import Completed.");
            } else {
                $output->writeln("Sorry no records found to import or already imported all records.");
            }

        }
        catch (\Exception $e){
            $output->writeln("Exception occurred. Please Contact Epicor for assistance.");
            $output->writeln($e->getMessage());
        }
    }
}
