<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper;


class LatestVersionCheck extends \Epicor\Common\Helper\Data
{
    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    protected $moduleList;

    public function __construct(
        \Epicor\Common\Helper\Context $context,
        \Magento\Framework\Module\ModuleList $moduleList
    ) {
        $this->moduleList=$moduleList;
        parent::__construct($context);
    }
    /**
     * Convert check the system has the latest packages for each epicor module 
     * 
     * @return array
     */
    public function packageCheck()
    {

        $typeOfRelease = 's';                    // release can be a - alpha, b - beta, s - stable
        $stableEpicorPackages = array();
        $epicorModuleVersions = array();
        $epcorPackagesNotLocal = array();
        $epcorPackagesXml = array();

        //get list of local modules
        //M1 > M2 Translation Begin (Rule P2-5.6)
        //$modules_config_xml = current(Mage::getConfig()->getXpath('modules'))->asArray();
        $modules_config_xml = current($this->moduleList->getAll())->asArray();
        //M1 > M2 Translation End

        foreach ($modules_config_xml as $moduleName => $moduleData) {
            if (substr($moduleName, 0, 7) == "Epicor_") {
                $epicorModuleVersions[strtolower($moduleName)] = $moduleData['version'];
            }
        }

        //get list of available packages
        $epicorPackagesUrl = 'http://update.epicorcommerce.com/ecc/packages.xml';
        $epicorUrlXml = simplexml_load_string(file_get_contents($epicorPackagesUrl));

        //need to encode to json and then immediately decode it to make xml array useable
        $tempArray = current(json_decode(json_encode($epicorUrlXml), true));
        foreach ($tempArray as $packageElement) {
            if ($packageElement['r'][$typeOfRelease]) {
                $epicorPackagesXml[strtolower($packageElement['n'])] = $packageElement['r'][$typeOfRelease];
            }
        }
        if (!empty($epicorPackagesXml)) {     // only process if packages are available
            // remove elements from local xml array that aren't on releases xml
            $packagesOnLocalSystem = array_intersect_key($epicorPackagesXml, $epicorModuleVersions);

            foreach ($packagesOnLocalSystem as $key => $value) {
                if ($value > $epicorModuleVersions[$key]) {
                    $stableEpicorPackages[$key] = $key . "-" . $value;
                }
            }
            $implodedPackages = implode('<br/>', $stableEpicorPackages);

            //M1 > M2 Translation Begin (Rule p2-1)
            //$config = Mage::getModel('core/config');
            $config = $this->resourceConfig;
            //M1 > M2 Translation End
            $versionsAvailable = $this->scopeConfig->getValue('Epicor_Comm/epicor_module_versions/versionsavailable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($versionsAvailable != $implodedPackages) {
                $config->saveConfig('Epicor_Comm/epicor_module_versions/versionsavailable', $implodedPackages);
                $title = 'New Releases Available for the Following Epicor Modules:';
                $this->sendMagentoMessage($implodedPackages, $title, \Magento\AdminNotification\Model\Inbox::SEVERITY_NOTICE, $link = null);
                $this->cache->clean();
            }
        }
    }

}
