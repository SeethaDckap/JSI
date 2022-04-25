<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Epicor Hosting Certificate Model
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 * 
 * @method string getName()
 * @method string getRequest()
 * @method string getPrivateKey()
 * @method string getCertificate()
 * @method string getCACertificate()
 * @method int getIssueNumber()
 * @method setName(string $name)
 * @method setRequest(string $requst)
 * @method setPrivateKey(string $key)
 * @method setCertificate(string $cert)
 * @method setCACertificate(string $ca_cert)
 * @method setIssueNumber(int $issue_number)
 */
class Certificate extends AbstractModel
{

    protected $_digest_alg = 'sha256';
    protected $_key_size = 2048;

    /**
     * @var \Epicor\HostingManager\Helper\Data
     */
    protected $hostingManagerHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Epicor\HostingManager\Model\ResourceModel\Site\CollectionFactory
     */
    protected $hostingManagerResourceSiteCollectionFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\HostingManager\Helper\Data $hostingManagerHelper,
        \Epicor\HostingManager\Model\ResourceModel\Site\CollectionFactory $hostingManagerResourceSiteCollectionFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->hostingManagerHelper = $hostingManagerHelper;
        $this->logger = $context->getLogger();
        $this->hostingManagerResourceSiteCollectionFactory = $hostingManagerResourceSiteCollectionFactory;
        $this->directoryList = $directoryList;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {
        $this->_init('Epicor\HostingManager\Model\ResourceModel\Certificate');
    }

    public function afterSave()
    {
        $helper = $this->hostingManagerHelper;
        /* @var $helper Epicor_HostingManager_Helper_Data */

        if ($this->hasPrivateKeyChanged() || $this->isObjectNew()) {
            $helper->savePrivateKey($this);
        }

        if ($this->hasCertificateChanged() || $this->hasCACertificateChanged() || $this->isObjectNew()) {
            $helper->saveCertificate($this);
        }

        if ($this->isInUse()) {
            $filename ="www-data-RELOAD-NGINX";
            $tmpfname = sys_get_temp_dir()."/".$filename; 
            $handle = fopen($tmpfname, "w");
            fwrite($handle, "writing to tempfile");
            fclose($handle);
            //shell_exec('sudo /etc/init.d/nginx reload');
        }

        parent::afterSave();
    }

    public function isValidCertificate()
    {
        return openssl_x509_check_private_key($this->getCertificate(), $this->getPrivateKey());
    }

    public function createSelfSignCertificate()
    {
        $config = array(
            'digest_alg' => $this->_digest_alg,
        );
        $issue_number = $this->getIssueNumber() + 1;
        $cert_res = openssl_csr_sign($this->getRequest(), null, $this->getPrivateKey(), 365, $config, $issue_number);
        openssl_x509_export($cert_res, $cert);

        if ($cert) {
            $this->setIssueNumber($issue_number);
            $this->setCertificate($cert);
        }
        return (bool) $cert;
    }

    public function generateCSR()
    {
        $key = false;
        $csr = false;
        try {

            $helper = $this->hostingManagerHelper;
            /* @var $helper Epicor_HostingManager_Helper_Data */

            //M1 > M2 Translation Begin (Rule p2-5.5)
            //$baseDir = Mage::getBaseDir('base') . DS;
            $baseDir = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT) . DIRECTORY_SEPARATOR;
            //M1 > M2 Translation End

            $randomFilename = $helper->getTempFileName();
            $logDir = $baseDir . 'var' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;
            $logFile = $logDir . 'cert.log';

            $configFile = $randomFilename . '.config';
            $keyFile = $randomFilename . '.key';
            $csrFile = $randomFilename . '.csr';

            if (!file_exists($logDir)) {
                mkdir($logDir);
            }

            # Generate CSR

            $config = array(
                "C" => $this->getCountry(),
                "ST" => $this->getState(),
                "L" => $this->getCity(),
                "O" => $this->getOrganisation(),
                "OU" => $this->getDepartment(),
                "CN" => $this->getDomain_name(),
                "E" => $this->getEmail()
            );
            $helper->createCsrConfigfile($randomFilename . '.config', $config, $this->_key_size, $this->_digest_alg);

            $csrCmd = 'openssl req -new -config ' . $configFile . ' -keyout ' . $keyFile . ' -out ' . $csrFile . ' > ' . $logFile . ' 2>&1';

            $output = array();
            exec($csrCmd, $output);

            if (file_exists($keyFile)) {
                $key = file_get_contents($keyFile);
                if ($key) {
                    $this->setPrivateKey(trim($key));
                }
            } else {
                throw new \Exception('Key file not created');
            }

            if (file_exists($csrFile)) {
                $csr = file_get_contents($csrFile);
                if ($csr) {
                    $this->setRequest(trim($csr));
                }
            } else {
                throw new \Exception('CSR file not created');
            }
        } catch (Mage_Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new \Exception('Error happened when creating CSR');
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new \Exception('Error happened when creating CSR');
        }

        $helper->deleteTempFiles($randomFilename);

        return (bool) $csr && (bool) $key;
    }

    /**
     * Returns true is being used by a site
     * 
     * @return bool
     */
    public function isInUse()
    {
        $sites = $this->hostingManagerResourceSiteCollectionFactory->create();
        /* @var $sites Epicor_HostingManager_Model_Resource_Site_Collection */
        $sites->addFieldToFilter('certificate_id', $this->getId());

        return (bool) $sites->count();
    }

    /**
     * Checks if a change in current data and original data has occured
     * 
     * @return bool
     */
    public function hasPrivateKeyChanged()
    {
        return $this->getData('private_key') != $this->getOrigData('private_key');
    }

    /**
     * Checks if a change in current data and original data has occured
     * 
     * @return bool
     */
    public function hasCertificateChanged()
    {
        return $this->getData('certificate') != $this->getOrigData('certificate');
    }

    /**
     * Checks if a change in current data and original data has occured
     * 
     * @return bool
     */
    public function hasCACertificateChanged()
    {
        return $this->getData('c_a_certificate') != $this->getOrigData('c_a_certificate');
    }

    public function beforeDelete()
    {

        $helper = $this->hostingManagerHelper;
        /* @var $helper Epicor_HostingManager_Helper_Data */
        $helper->deleteCertificateFiles($this);
        parent::beforeDelete();
    }

    /**
     * Converts field names for setters and geters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unneccessary preg_replace
     *
     * @param string $name
     * @return string
     */
    protected function _underscore($name)
    {
        if (isset(self::$_underscoreCache[$name])) {
            return self::$_underscoreCache[$name];
        }
        #Varien_Profiler::start('underscore');
//        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
//        
        $result = $name;
        while (preg_match('/([a-zA-Z0-9])([A-Z])/', $result)) {
            $result = preg_replace('/([a-zA-Z0-9])([A-Z])/', '$1_$2', $result);
        }
        $result = strtolower($result);
        #Varien_Profiler::stop('underscore');
        self::$_underscoreCache[$name] = $result;
        return $result;
    }

}
