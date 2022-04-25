<?php
/**
 * custom email template issue with order, shipment and invoice
 * https://github.com/magento/magento2/issues/26882
 * ECC-8926 - magento issue with custom email template
 * Script should require till Magento fix issue.
 */
require_once('_setup.php');
/* @var $resource \Magento\Framework\App\ResourceConnection */
/* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */

try
{
    $writeConnection->startSetup();
    $writeConnection->beginTransaction();

    $query = "UPDATE `email_template` SET `is_legacy` = '1' where `is_legacy` = '0'";

    $writeConnection->query($query);

    $writeConnection->commit();
    $writeConnection->endSetup();

}
catch (\Exception $e){
    echo "<b> Exception occured. Please check is 'SQL QUERY'.</b>";
    die;
}

echo "</br> FINISHED SUCCESSFULLY";
