<?php

/**
 * DEV TOOL: Run Manual SQL
 * 
 * DO NOT RELEASE TO PRODUCTION!
 * 
 * @author Epicor.ECC.Team
 * Curl/ fiddler/ HTTP requester URL @url
 * @url: http://ecc.magento2.dev/eccResponder.php
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

function runQuery($queries, $writeConnection)
{
    foreach ($queries as $qry) {
        $writeConnection->query($qry);
    }
}
