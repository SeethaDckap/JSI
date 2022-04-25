<?php


/**
 *To change the backend model of catalog product & catalog category common eav attributes
 */
require_once('_setup.php');
/* @var $resource \Magento\Framework\App\ResourceConnection */
/* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */

$writeConnection->startSetup();
$writeConnection->beginTransaction();

// Concatenated with . for readability
$query = "update eav_attribute  set backend_model = :bkmodel where attribute_code IN ('ecc_erp_images','ecc_previous_erp_images') and entity_type_id IN (3,4)"; 
  
$binds = array( 'bkmodel'    => "Epicor\Comm\Model\Eav\Attribute\Data\Erpimages");
$writeConnection->query($query, $binds);


$writeConnection->commit();
$writeConnection->endSetup();

echo "FINISHED SUCESSFULLY";
