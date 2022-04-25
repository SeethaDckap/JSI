<?php
/**
 *To downgrade the Epicor Common Module version to 2.6.0 version
 */
require_once('_setup.php');
/* @var $resource \Magento\Framework\App\ResourceConnection */
/* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */

try
{
    $writeConnection->startSetup();
    $writeConnection->beginTransaction();

    // Concatenated with . for readability
    $query = "update setup_module  set schema_version = :schema_version , data_version = :data_version where module = 'Epicor_Common'";

    $binds = array( 'schema_version'    => "2.6.0", 'data_version' => "2.6.0");
    $writeConnection->query($query, $binds);

    $writeConnection->commit();
    $writeConnection->endSetup();

}
catch (\Exception $e){
    echo "<b> Exception occured. Please check is 'SQL QUERY'.</b>";
    die;
}

echo "Epicor Common Module version downgraded to 2.6.0";
