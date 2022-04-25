<?php 
ini_set('memory_limit', '512M');
ini_set('display_errors',1); 
error_reporting(E_ALL);

/**
 * Comm/data/Responder action standalone file for Magento 2.1
 * @author Epicor.ECC.Team
 * Curl/ fiddler/ HTTP requester URL @url
 * @url: http://ecc.magento2.dev/eccResponder.php
 */

use Magento\Framework\App\Bootstrap;
 
require __DIR__ . '/../app/bootstrap.php';
 
$params = $_SERVER;
 
$bootstrap = Bootstrap::create(BP, $params);
 
$objectManager  = $bootstrap->getObjectManager();


$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();
$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/customer_hash_fix.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);

$logger->info('script running ..............');

echo "\n script running";
$query = "select password_hash,email,entity_id from customer_entity where password_hash not like '%1:2' and password_hash not like '%:2' and password_hash not like '%:1'";
   //. ' LIMIT 0,2'
$logger->info('Select Query  = '.$query);

$results = $connection->fetchAll($query);

if(count($results) >0){
foreach ($results as $row) {
    if (isset($row['entity_id']) && !empty($row['entity_id'])) {
       $updateQuery = 'UPDATE '
                . $resource->getTableName('customer_entity')
                . " SET password_hash = CONCAT('". $row['password_hash']."',':1')"
                . " WHERE email=\"" . $row['email'] . "\"";
        $logger->info('Update Query  = '.$updateQuery);

        $updateResults = $connection->query($updateQuery);
        $affectedRows = $updateResults->rowCount();
        if ($affectedRows > 0) {
            $logger->info("hash updated  customer_id ".$row['entity_id']." customer_email " . $row['email'] . " password hash " . $row['password_hash']);
			$logger->info('==============================================');
        }
	
    }
}
}
else{
	$logger->info('No Md5 format hash found');
echo "\n No Md5 format hash found";
}
$logger->info('Script executed..............');
echo "\n Script executed.Kindly check /var/www/test/var/log/customer_hash_fix.log for more details";
?>
