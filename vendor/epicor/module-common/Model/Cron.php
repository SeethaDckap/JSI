<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model;


class Cron
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;
    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->commonHelper = $commonHelper;
        $this->cache = $cache;
        $this->generic = $generic;
        $this->moduleList=$moduleList;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->resourceConfig = $resourceConfig;
        $this->directoryList = $directoryList;
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
                $epicorModuleVersions[strtolower($moduleName)] = (array_key_exists('version', $moduleData) ? $moduleData['version'] : 0);
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
                $this->commonHelper->sendMagentoMessage($implodedPackages, $title, \Magento\Framework\Notification\MessageInterface::SEVERITY_NOTICE, $link = null);
                $this->cache->clean();
            }
        }
    }

    /**
     * @param $path
     * @param null $storeId
     * @return mixed|string
     */
    public function getDirectConfigValue($path, $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }
        $config = $this->getDbConfigValue($path, $storeId);
        if(!$config){
            return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        return $config;
    }

    /**
     * @param $path
     * @param $storeId
     * @return mixed|string
     */
    private function getDbConfigValue($path, $storeId)
    {
        $result = [];
        try {
            if (!$path || !is_numeric($storeId)) {
                throw new \InvalidArgumentException('Config path or store id not set');
            }
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('core_config_data');
            $sql = "SELECT value FROM " . $tableName . " WHERE path = '$path' AND scope_id = $storeId";
            $result = $connection->fetchCol($sql);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result[0] ?? '';
    }


    /**
     * @return int
     */
    private function getStoreId()
    {
        try {
            $store = $this->storeManager->getStore();

            if ($store instanceof \Magento\Store\Api\Data\StoreInterface) {
                return $store->getId();
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

    }

    public function imageCleanup()
    {


        //M1 > M2 Translation Begin (Rule p2-6.10)
        // Mage::app()->setCurrentStore(1);
        $this->storeManager->setCurrentStore(1);
        //M1 > M2 Translation End


        /* Lets the session start to avoid errors */
        $session = $this->generic;
        /* @var $session Mage_Core_Model_Session */

        /**
         * Get the resource model
         */
        $resource = $this->resourceConnection;

        /**
         * Execute the query
         */
        $return_data = $this->resourceConnection->getConnection('core_read')->fetchAll('SELECT value FROM catalog_product_entity_media_gallery');


        $files = array();

        foreach ($return_data as $val) {
            $files[$val['value']] = 1;
        }
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //$media_directory = Mage::getBaseDir("media") . DS . 'catalog' . DS . 'product';
        $media_directory = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';
        //M1 > M2 Translation End
        exec("find {$media_directory} -type f", $output);
        $validSuffixArray = array_map('trim', explode(',', $this->scopeConfig->getValue('Epicor_Comm/assets/suffixes', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)));
        foreach ($output as $filename) {
            $filenameNoPath = basename($filename, '.');
            $fileType = explode('.', $filenameNoPath);
            if (in_array(end($fileType), $validSuffixArray)) {
                $file = str_replace($media_directory, '', $filename);
                if (
                    !@$files[$file] == 1 &&
                    strpos($file, DIRECTORY_SEPARATOR . 'placeholder' . DIRECTORY_SEPARATOR) === false &&
                    strpos($file, DIRECTORY_SEPARATOR . 'watermark' . DIRECTORY_SEPARATOR) === false
                ) {
                    echo "Orphan jpg deleted: <br />";
                    $this->logger->log(\Psr\Log\LogLevel::INFO, "Orphan image deleted: ");
                    echo $media_directory . "/" . $filename, '<br />';
                    $this->logger->log(\Psr\Log\LogLevel::INFO, $filename);
                    unlink($filename);
                }
            }
        }
    }

}
