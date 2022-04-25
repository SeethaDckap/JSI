<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '5G');
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;
require dirname ( dirname ( dirname( dirname(__FILE__) ) ) ) . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

$setup = $objectManager->get('Magento\Framework\Setup\SchemaSetupInterface');
$eavsetupFactory = $objectManager->get('\Magento\Eav\Setup\EavSetupFactory');

 $installer = $setup;
 $installer->startSetup();
 
//Remove new columns on ecc_erp_account (no need to set old values)
$newEccErpAccountColumns = ['allow_shipping_address_edit'=>'Allow Shipping Address Edit'
                           ,'allow_shipping_address_create'=> 'Allow Shipping Address Create'
                           ,'allow_billing_address_edit'=>'Allow Billing Address Edit'
                           ,'allow_billing_address_create'=>'Allow Billing Address Create'
        ];
		
		
$tableName = $installer->getTable('ecc_erp_account');
if ($installer->tableExists($tableName)) {
	foreach($newEccErpAccountColumns as $newEccErpAccountColumn=>$colLabel){
		 if ($installer->tableExists($tableName)) {
			if ($installer->getConnection()->tableColumnExists($tableName, $newEccErpAccountColumn) == true){
				var_dump('erp column removed: ', $newEccErpAccountColumn);
				$installer->getConnection()->dropColumn($tableName, $newEccErpAccountColumn);
			}
		 }
	}	
 }
//remove added customer attributes
 $attributesToRemove = ['ecc_allow_shipping_address_edit', 'ecc_allow_shipping_address_create', 'ecc_allow_billing_address_edit', 'ecc_allow_billing_address_create'];
	 
$eavSetup = $eavsetupFactory->create();
foreach($attributesToRemove as $attribute){
	var_dump('attribute removed:', $attribute);
//	$entityTypeId = 1; 
	$eavSetup->removeAttribute('customer', $attribute);
}	   
$installer->endSetup();   
