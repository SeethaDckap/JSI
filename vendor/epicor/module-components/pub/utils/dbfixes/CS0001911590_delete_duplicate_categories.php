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
 * This script is to delete the duplicate categories
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

/*$state = $obj->get('\Magento\Framework\App\State');
$state->setAreaCode('frontend');*/
try
{
    $writeConnection->startSetup();
    $writeConnection->beginTransaction();

    $sql = "SELECT 
    `e`.*, `at_ecc_erp_code`.`value` AS `ecc_erp_code`
FROM
    `catalog_category_entity` AS `e`
        LEFT JOIN
    `catalog_category_entity_text` AS `at_ecc_erp_code` ON (`at_ecc_erp_code`.`entity_id` = `e`.`entity_id`)
        AND (`at_ecc_erp_code`.`attribute_id` = (select attribute_id from eav_attribute where attribute_code = 'ecc_erp_code'))
        AND (`at_ecc_erp_code`.`store_id` = 0);";
    $stores = $obj->get(\Magento\Store\Model\StoreManagerInterface::class)->getStores();
    $categories = $writeConnection->fetchAll($sql);

    foreach ($stores as $store) {
        $rootCatId = $store->getRootCategoryId();
        foreach ($categories as $category){
            $groupCode = $category['ecc_erp_code'];
            if (!is_null($groupCode)) {
                $erpCategories = $obj->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory')->create();
                $erpCategories
                    ->addAttributeToFilter('ecc_erp_code', array('eq' => $groupCode))
                    ->addAttributeToFilter('path', array('like' => "%/".$rootCatId."/%"))
                    ->addAttributeToSelect('*');
                if ($erpCategories->count() > 1) {
                    foreach ($erpCategories->getItems() as $_category) {
                        if ($_category->getLevel() == 2) {
                            $delete = 'DELETE FROM catalog_category_entity WHERE entity_id = '.$_category->getEntityId();
                            $writeConnection->query($delete);
                        }
                    }
                }
            }
        }
    }
    $writeConnection->commit();
    $writeConnection->endSetup();
    echo 'Done';
}
catch (\Exception $e){
    echo "<b> Exception occured. Please check is 'SQL QUERY'.</b>";
    die;
}
