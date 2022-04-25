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
 * This script is to fix the related document having wrong url
 * This resyncs the related doc so that url is corrected
 */
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../../../app/bootstrap.php';

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
    $helper = $obj->get('\Epicor\Comm\Helper\Product\Relateddocuments\Sync');
    $collection = $obj->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')->create();
    $collection->addAttributeToFilter(array(
        array('attribute' => 'ecc_related_documents', 'null' => 1),
        array('attribute' => 'ecc_related_documents', 'neq' => 'a:0:{}')
    ),null, 'left');
    //$collection->addAttributeToFilter('entity_id', 34);
    $collection->addAttributeToFilter('entity_id', [['from' => '1', 'to' => '200']]);
    $products = $collection->getItems();
    $assetsFolder = $helper->getAssetsFolder();
    if ($helper->validateOrCreateDirectory($assetsFolder)) {
        foreach ($products as $productInfo) {
            /* @var $productInfo Varien_Object */
            echo $productInfo->getSku() . "\n";
            $productId = $obj->get('\Magento\Catalog\Model\ProductFactory')->create()->setStoreId(0)->getIdBySku($productInfo->getSku());
            $helper->processRelatedDocuments($productId, true);
        }
    }
    echo 'Done';
}
catch (\Exception $e){
    echo $e->getMessage();
    echo "<b> Exception occured. Please check is 'SQL QUERY'.</b>";
    die;
}
