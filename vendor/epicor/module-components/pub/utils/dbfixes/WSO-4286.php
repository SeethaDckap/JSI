<?php

/**
 * adding the customer attribute ecc_master_shopper to admin form so it will be saved after save 
 */
require_once('_setup.php');
/* @var $resource \Magento\Framework\App\ResourceConnection */
/* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */



$writeConnection->startSetup();
$writeConnection->beginTransaction();

$sql ='SELECT count(*) FROM customer_form_attribute WHERE form_code=\'adminhtml_customer\' AND attribute_id = (select attribute_id from eav_attribute where attribute_code=\'ecc_master_shopper\')';

$count = $writeConnection->fetchOne($sql);
if($count == 0 ) {
    $var = $writeConnection->query("insert into customer_form_attribute(form_code,attribute_id) values('adminhtml_customer',(select attribute_id from eav_attribute where attribute_code='ecc_master_shopper'))");
}
$writeConnection->commit();
$writeConnection->endSetup();

echo "FINISHED SUCESSFULLY";