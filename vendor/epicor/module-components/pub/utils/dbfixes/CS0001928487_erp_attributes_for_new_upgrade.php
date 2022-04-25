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
    $sql = "select * from ecc_erp_mapping_attributes_old";
    $data = [];
    $result = $writeConnection->fetchAll($sql);
    if (count($result) > 0) {
        foreach ($result as $_data) {
            $erp = [];
            $erp['attribute_code'] = $_data['attribute_code'];
            $erp['input_type'] = $_data['input_type'];
            $erp['separator'] = $_data['separator'];
            $erp['is_searchable'] = $_data['quick_search'];
            $erp['search_weight'] = $_data['search_weighting'];
            $erp['is_visible_in_advanced_search'] = $_data['advanced_search'];
            $erp['is_comparable'] = 0;
            $erp['is_filterable'] = $_data['use_in_layered_navigation'];
            $erp['is_filterable_in_search'] = $_data['search_results'];
            $erp['position'] = 0;
            $erp['is_used_for_promo_rules'] = 0;
            $erp['is_html_allowed_on_front'] = 0;
            $erp['is_visible_on_front'] = $_data['visible_on_product_view'];
            $erp['used_in_product_listing'] = 0;
            $erp['used_for_sort_by'] = 0;
            $data[] = $erp;
        }
        $writeConnection->insertMultiple(
            'ecc_erp_mapping_attributes',
            $data
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
