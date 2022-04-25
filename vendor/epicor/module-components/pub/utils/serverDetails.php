<?php

ini_set('memory_limit', '512M');

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../../app/bootstrap.php';

$params = $_SERVER;

$bootstrap = Bootstrap::create(BP, $params);

$obj = $bootstrap->getObjectManager();

$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

print_r(require_auth($obj));

function require_auth($obj) {

    $storeManager = $obj->get('\Magento\Store\Model\StoreManagerInterface');
    $eccHelper = $obj->get('\Epicor\Common\Helper\Data');
    $startTime = gmdate("m/d/Y h:i:s");
    $baseUrl = $storeManager->getStore()->getBaseUrl();   
    $AUTH_USER = 'admin';
    $AUTH_PASS = '';
    header('Cache-Control: no-cache, must-revalidate, max-age=0');
    $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
    if($has_supplied_credentials){
        $securityKey = $eccHelper->eccDecode($_SERVER['PHP_AUTH_PW']);
        $securityKeyData = explode("_", $securityKey, 2);
        $requestTime = $securityKeyData[0];
        $convertedTime = date("m/d/Y h:i:s",strtotime($requestTime)+20);
        $requestUrl = $securityKeyData[1];
        if($AUTH_USER == $_SERVER['PHP_AUTH_USER'] && $requestUrl == $baseUrl && $startTime <= $convertedTime){
            print_r(json_encode(getEnvironmentDetails($obj)));
        }else{
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');
            return 'Invalid Credentials';
        }
    }
}

function getEnvironmentDetails($obj) {
    $modelData = array();
    $modelData['osInformation'] = getOSInformation();
    $modelData['phpVersion'] = getPhpVersion();
    $modelData['mysqlVersion'] = getMySQLVersion();
    $modelData['magentoVersion'] = getMagentoVersion($obj);
    $modelData['eccVersion'] = getEccVersion($obj);
    $modelData['magentoPackages'] = getMagentoPackages($obj);
    $modelData['erpUrl'] = getErpUrl($obj);
    $modelData['hardwareDetails'] = getHardwareInformation();
    return $modelData;
}

function getOSInformation() {
    if (false == function_exists("shell_exec") || false == is_readable("/etc/os-release")) {
        return null;
    }

    $os = shell_exec('cat /etc/os-release');
    $listIds = preg_match_all('/.*=/', $os, $matchListIds);
    $listIds = $matchListIds[0];

    $listVal = preg_match_all('/=.*/', $os, $matchListVal);
    $listVal = $matchListVal[0];

    array_walk($listIds, function(&$v, $k) {
        $v = strtolower(str_replace('=', '', $v));
    });

    array_walk($listVal, function(&$v, $k) {
        $v = preg_replace('/=|"/', '', $v);
    });

    return array_combine($listIds, $listVal);
}

function getPhpVersion() {
    return phpversion();
}

function getMySQLVersion() {
    $output = shell_exec('mysql -V');
    preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
    return $version[0];
}

function getMagentoVersion($obj) {
    $productMetadata = $obj->get('Magento\Framework\App\ProductMetadataInterface');
    return $productMetadata->getVersion();
}

function getEccVersion($obj) {
    $globalConfig = $obj->get('Epicor\Comm\Model\GlobalConfig\Config');
    $versionInfo = $globalConfig->get('ecc_version_info');
    return $versionInfo;
}

function getErpUrl($obj) {
    $scopeConfig = $obj->get('Magento\Framework\App\Config\ScopeConfigInterface');
    $erpUrl = $scopeConfig->getValue('Epicor_Comm/xmlMessaging/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    return $erpUrl;
}

function getHardwareInformation() {
    $filepath = getcwd() . '/scripts/hardwareDetails.sh';
    if (false == function_exists("shell_exec") || false == is_writable($filepath)) {
        return $filepath . " File does not exists or Unable to execute the script please check file permissions";
    }
    if (file_exists($filepath)) {
        $output = shell_exec($filepath);
        return $output;
    } else {
        return $filepath . " File does not exists";
    }
}

function getMagentoPackages($obj) {
    $magentoPackages = $obj->get('Magento\Framework\Composer\ComposerInformation');
    return $magentoPackages->getInstalledMagentoPackages();
}