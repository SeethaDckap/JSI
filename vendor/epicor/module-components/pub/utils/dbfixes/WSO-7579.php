<?php


/**
 *To change the front end label of customer 'ecc_allow_masq_cart_reprice' attribute
 */
require_once('_setup.php');
/* @var $resource \Magento\Framework\App\ResourceConnection */
/* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */

$writeConnection->startSetup();
$writeConnection->beginTransaction();

$query = "UPDATE eav_attribute SET attribute_code = 'supplierpartnumber' WHERE attribute_code = 'supplierpartnumbermapped'";

$writeConnection->query($query);

$writeConnection->commit();
$writeConnection->endSetup();

echo "FINISHED SUCESSFULLY";