<?php
/**
 * ERP Attributes Mapping mirgration
 */
/**
 * DEV TOOL: Run Manual SQL
 *
 * DO NOT RELEASE TO PRODUCTION!
 *
 * Run this from pub directory
 *
 * @author Epicor.ECC.Team
 */

/**
 * This script is to migrate the datas of old ecc_erp_mapping_attributes to new ecc_erp_mapping_attributes
  */
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';

$params = $_SERVER;

$bootstrap = Bootstrap::create(BP,
    $params);

$obj = $bootstrap->getObjectManager();

$resource = $obj->get('\Magento\Framework\App\ResourceConnection');
/* @var $resource \Magento\Framework\App\ResourceConnection */

$writeConnection = $resource->getConnection('core_write');
/* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */

try
{
    $writeConnection->startSetup();
    $writeConnection->beginTransaction();
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
where eav.ecc_created_by = 'STK';";
    $data = [];
    $result = $writeConnection->fetchAll($sql);
    if (count($result) > 0) {
        $writeConnection->insertMultiple(
            'ecc_erp_mapping_attributes',
            $result
        );
    }
    $writeConnection->commit();
    $writeConnection->endSetup();
    echo 'Done';
}
catch (\Exception $e){
    echo "<b> Exception occured. Please check is 'SQL QUERY'.</b>";
    die;
}
