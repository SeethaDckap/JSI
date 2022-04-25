<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;


/**
 * Request HRT - Heart Beat 
 * 
 * Get the account information for the specified customer account
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method array getLicenseTypes()
 * @method setLicenseTypes(array $licenseTypes)
 * 
 * @method array getValidLicenseTypes()
 * @method setValidLicenseTypes(array $licenseTypes)
 */
class Lics extends \Epicor\Comm\Model\Message\Request
{

    /**
     *
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $_helper;

    /**
     *
     * @var \Epicor\Common\Helper\Messaging\Cache
     */
    protected $_cacheHelper;
    private $_licenseTypes = array(
        'consumer_connect' => 'Consumer',
        'customer_connect' => 'Customer',
        'supplier_connect' => 'Supplier',
        'mobile_connect_ios' => 'Ios',
        'mobile_connect_android' => 'Android',
        'consumer_configurator' => 'Consumer_Configurator',
        'customer_configurator' => 'Customer_Configurator',
        'dealer_portal' => 'Dealer_Portal',
    );

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * Construct object and set message type.
     */
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->resourceConfig=$resourceConfig;
        $this->registry = $context->getRegistry();

        parent::__construct($context, $resource, $resourceCollection, $data);

        $this->setMessageType('LICS');
        $this->setLicenseType(array());
        $this->setConfigBase('epicor_comm_enabled_messages/lics_request/');
        $this->_helper = $this->commMessagingHelper;
//        $this->_cacheHelper = Mage::helper('epicor_common/messaging_cache');

    }
    public function buildRequest()
    {
        $message = $this->getMessageTemplate();
        $message['messages']['request']['body'] = array_merge($message['messages']['request']['body'], array(
            'licenseDetails' => '',
        ));

        $this->setOutXml($message);
        return true;
    }

    public function processResponse()
    {
        $success = false;
        $license_types = array();
        $valid_license_types = array();

        if ($this->isSuccessfulStatusCode()) {

            $response = $this->getResponse();
//            if (!$this->_cached) {
//                $this->_cacheHelper->setCache('LICS', array('Results'), $response);
//            }
//           
            // Process Xml
            foreach ($response->getLicenseDetails()->getData() as $type => $enabled) {

                if (array_key_exists($type, $this->_licenseTypes))
                    $license_type = $this->_licenseTypes[$type];
                else
                    $license_type = $type;

                $license_types[$license_type] = $enabled;

                if ($enabled == 'Y')
                    $valid_license_types[] = $license_type;

                $this->setLicenseTypes(array());
            }

            //M1 > M2 Translation Begin (Rule P2-2)
            //$config = Mage::getConfig();
            $config=$this->resourceConfig;
            //M1 > M2 Translation End

            $pools = '';
            if ($response->getLicenseCounts())
                $pools = $response->getLicenseCounts()->getData();

            $config->saveConfig('Epicor_Comm/xmlMessaging/pools', serialize($pools), \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0);

            $success = true;
            $this->registry->register('check_licensing_config', true);
        }
        $this->setLicenseTypes($license_types);
        $this->setValidLicenseTypes($valid_license_types);
        return $success;
    }

    /**
     * Updates the response object from any cached data
     */
//    public function updateResponseFromCache()
//    {
//        parent::updateResponseFromCache();
//        if ($this->_cached)
//            $this->setResponse($this->_cacheHelper->getCache('LICS', array('Results')));
//    }
}
