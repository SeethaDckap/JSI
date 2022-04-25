<?php
ini_set('memory_limit', '512M');

/**
 * DEV TOOL: remove previously added blog menu item
 *
 * DO NOT RELEASE TO PRODUCTION!
 *
 * @author Epicor.ECC.Team
 * Curl/ fiddler/ HTTP requester URL @url
 * @url: http://ecc.magento2.dev/eccResponder.php
 */

use \Magento\Framework\App\Bootstrap;

require __DIR__ . '/../../../app/bootstrap.php';

$params = $_SERVER;

$bootstrap = Bootstrap::create(BP, $params);

$obj = $bootstrap->getObjectManager();

$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$menurepo = $obj->get('Epicor\Themes\Model\ResourceModel\Greenblack\Menu\Collection');
$menurepo->addFieldToFilter('name', 'Blog');
$blogItem = $menurepo->getFirstItem();
if ($blogItem->isObjectNew()) {
        echo 'Blog menu item not removed as it does not exist';
} else {
        $blogItem->delete();
        echo 'Blog menu item removed';
}
?>
