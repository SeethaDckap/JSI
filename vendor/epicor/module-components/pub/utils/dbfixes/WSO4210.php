<?php

/**
 * Removing the few custom customer address attribute from frontend forms
 */
require_once('_setup.php');
/* @var $resource \Magento\Framework\App\ResourceConnection */
/* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */



$writeConnection->startSetup();
$writeConnection->beginTransaction();
$var = $writeConnection->query("delete from  customer_form_attribute where form_code in ('customer_address_edit', 'customer_register_address') and attribute_id in (select attribute_id from eav_attribute where attribute_code in ('ecc_erp_group_code','ecc_erp_address_code','ecc_is_registered','ecc_is_delivery','ecc_is_invoice'))");

$writeConnection->commit();
$writeConnection->endSetup();

echo "FINISHED SUCESSFULLY";