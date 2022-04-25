<?php


/**
 * WSO-8845 - Address created during checkout is wrongly being saved with E
 * RP Address Code which means that it cannot be edited via address book
 *
 * To change ecc_erp_address_code for Those set default code M234 and M235 conflict.
 */
require_once('_setup.php');
/* @var $resource \Magento\Framework\App\ResourceConnection */
/* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql */
try {
    $writeConnection->startSetup();
    $writeConnection->beginTransaction();
    $affectedRows = 0;

    $attributeAddressCodeQuery = "SELECT attribute_id FROM `eav_attribute` WHERE `entity_type_id` = '2' AND `attribute_code` = 'ecc_erp_address_code'";
    $attributeAddressCode = $writeConnection->fetchRow($attributeAddressCodeQuery);

    $attributeGroupCodeQuery = "SELECT attribute_id FROM `eav_attribute` WHERE `entity_type_id` = '2' AND `attribute_code` = 'ecc_erp_group_code'";
    $attributeGroupCode = $writeConnection->fetchRow($attributeGroupCodeQuery);

    if (isset($attributeAddressCode["attribute_id"]) && isset($attributeGroupCode["attribute_id"])) {
        $attributeAddressCodeId = $attributeAddressCode["attribute_id"];
        $attributeGroupCodeId = $attributeGroupCode["attribute_id"];
        $dataSql = "Select main.entity_id, main.street, main.city, main.postcode, main.country_id, ce.email as email,
caev_g.value as 'ecc_erp_group_code', 
caev_a.value as 'ecc_erp_address_code', 
caev_g.attribute_id as 'ecc_erp_group_code_id' , 
caev_a.attribute_id as 'ecc_erp_address_code_id' 
From customer_address_entity as main 
left join customer_entity AS ce ON main.parent_id = ce .entity_id
left join customer_address_entity_varchar AS caev_g ON main.entity_id = caev_g.entity_id and caev_g.attribute_id = " . $attributeGroupCodeId . " 
left join customer_address_entity_varchar AS caev_a ON main.entity_id = caev_a.entity_id and caev_a.attribute_id = " . $attributeAddressCodeId . " 
WHERE caev_g.value IS NULL and caev_a.value IS NOT NULL";
        $data = $writeConnection->fetchAll($dataSql);

        $currentDate = date('Y_m_d_H_i_s');
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/wso_8845_'.$currentDate.'.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("###########START################");
        if (count($data) > 0) {
            $logger->info("Deleted row information");
            foreach ($data as $addressVarchar) {
                $logger->info("--------------START--------------");
                $logText = "SQL:Delete From customer_address_entity_varchar WHERE";
                $logText .= "`attribute_id`= '" . $addressVarchar["ecc_erp_address_code_id"] . "' AND";
                $logText .= "`value`= '" . $addressVarchar["ecc_erp_address_code"] . "' AND";
                $logText .= "`entity_id` = '" . $addressVarchar["entity_id"] . "'";
                $logger->info($logText);
                $logger->info("Email:" . $addressVarchar["email"]);
                $logger->info("Street:" . $addressVarchar["street"]);
                $logger->info("City:" . $addressVarchar["city"]);
                $logger->info("Postcode:" . $addressVarchar["postcode"]);
                $logger->info("Country_id:" . $addressVarchar["country_id"]);
                $affectedRow = $writeConnection->delete('customer_address_entity_varchar',
                    [
                        'attribute_id = ?' => $addressVarchar["ecc_erp_address_code_id"],
                        'value = ?' => $addressVarchar["ecc_erp_address_code"],
                        'entity_id = ?' => $addressVarchar["entity_id"]
                    ]);
                $affectedRows = $affectedRows + $affectedRow;
                $logger->info("--------------END--------------");
            }
        }
    }

    $writeConnection->commit();
    $writeConnection->endSetup();
    echo "TOTAL AFFECTED ROW'S: " . $affectedRows;
    echo "</br> FINISHED SUCCESSFULLY";
    $logger->info("TOTAL AFFECTED ROW'S: " . $affectedRows);
    $logger->info("###########END################");
} catch (\Exception $e) {
    echo "<b> Exception occured. Please check is 'SQL QUERY'.</b>";
    die;
}

