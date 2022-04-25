<?php


/**
 *To change the front end label of customer 'ecc_allow_masq_cart_reprice' attribute
 */
require_once('_setup.php');
/* @var $resource \Magento\Framework\App\ResourceConnection */
/* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */

$writeConnection->startSetup();
$writeConnection->beginTransaction();

// Concatenated with . for readability
$query = "update eav_attribute  set frontend_label = :flabel where attribute_code = 'ecc_allow_masq_cart_reprice' and entity_type_id = '1' "; 

$binds = array( 'flabel'    => "Allowed to Reprice Cart before on Masquerading as Child Account");
$writeConnection->query($query, $binds);


$writeConnection->commit();
$writeConnection->endSetup();

echo "FINISHED SUCESSFULLY";
