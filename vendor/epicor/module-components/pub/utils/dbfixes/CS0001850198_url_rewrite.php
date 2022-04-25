<?php
/**
 *To downgrade the Epicor Common Module version to 2.6.0 version
 */
/**
 * DEV TOOL: Run Manual SQL
 *
 * DO NOT RELEASE TO PRODUCTION!
 *
 * Run this from pub directory
 *
 * @author Epicor.ECC.Team
 * Curl/ fiddler/ HTTP requester URL @url
 * @url: http://ecc.magento2.dev/eccResponder.php
 */

/**
 * This script is to fix the url_rewrite table having metadata column with serialised data
 * In Magento 2 metadata column data should be json_encoded
 * This script takes the serialised data and converts it to json_encode and updates the column
 */
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';

$params = $_SERVER;

$bootstrap = Bootstrap::create(BP,
    $params);

$obj = $bootstrap->getObjectManager();

$resource = $obj->get('\Magento\Framework\App\ResourceConnection');
/* @var $resource \Magento\Framework\App\ResourceConnection */

$writeConnection = $resource->getConnection('core_write');
/* @var $writeConnection \Magento\Framework\DB\Adapter\Pdo\Mysql  */

try
{
    $writeConnection->startSetup();
    $writeConnection->beginTransaction();
    $sql = "select url_rewrite_id, entity_id, metadata from url_rewrite where metadata like 'a:1:{s:%' and entity_type = 'product'";
    // Try below before running for the whole table
    //$sql = "select url_rewrite_id, entity_id, metadata from url_rewrite where metadata like 'a:1:{s:%' and entity_type = 'product' and entity_id = 637";
    $result = $writeConnection->fetchAll($sql);
    foreach ($result as $meta) {
        $uns = unserialize($meta['metadata']);
        $_metadata = json_encode($uns);
        $query = "update url_rewrite  set metadata = '" . $_metadata . "'where url_rewrite_id = " . $meta['url_rewrite_id'];
        echo $meta['entity_id'] . "\n";
        $writeConnection->query($query);
    }
    $writeConnection->commit();
    $writeConnection->endSetup();
    echo 'Done';
}
catch (\Exception $e){
    echo "<b> Exception occured. Please check is 'SQL QUERY'.</b>";
    die;
}
