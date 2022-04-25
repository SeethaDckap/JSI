<?php
/**
 * Removing the bookmark for saved UI component -  epicor_elasticsearch_boost_listing
 *
 * Updating the component sorty-by value does not reflect
 * unless the record is deleted from ui_bookmark table this is an existing issue in Magento
 */

require_once('_setup.php');
/* @var $resource \Magento\Framework\App\ResourceConnection */
/* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */

try
{
    $writeConnection->startSetup();
    $writeConnection->beginTransaction();
    $query = "DELETE FROM `ui_bookmark` WHERE `namespace` = 'epicor_elasticsearch_boost_listing'";
    $writeConnection->query($query);
    $writeConnection->commit();
    $writeConnection->endSetup();
}
catch (\Exception $e){
    echo "<b> Something went wrong with the SQL QUERY.</b>";
    die;
}
echo "</br> FINISHED SUCCESSFULLY";
