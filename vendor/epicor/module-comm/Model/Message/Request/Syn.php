<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;


/**
 * Request Syn - Sync Message 
 * 
 * Get the account information for the specified customer account
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method setAll(bool $all)
 * @method bool getAll()
 * @method array getMessageTypes()
 * @method setFrom(string $from_date)
 * @method string getFrom()
 * @method setSyncUrl(string $url)
 * @method string getSyncUrl()
 * 
 * @method array getWebsites()
 * @method array getStores()
 * @method setWebsites(array $websites)
 * @method setStores(array $stores)
 * 
 * @method setTrigger(string $trigger)
 * 
 */
class Syn extends \Epicor\Comm\Model\Message\Request
{
//    const STATUS_SUCCESS = 'Y';
//    const STATUS_FAILURE = 'N';
//
//    public $success_status_codes = array(
//        self::STATUS_SUCCESS => 'Success',
//    );
//    public $error_status_codes = array(
//        self::STATUS_FAILURE => 'Failed',
//    );

    /**
     * @var \Epicor\Comm\Model\Syn\LogFactory
     */
    protected $commSynLogFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Epicor\Comm\Helper\Entityreg
     */
    protected $commEntityregHelper;

    /**
     * Construct object and set message type.
     */
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Comm\Model\Syn\LogFactory $commSynLogFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Helper\Entityreg $commEntityregHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->commSynLogFactory = $commSynLogFactory;
        $this->backendAuthSession = $backendAuthSession;
        $this->commEntityregHelper = $commEntityregHelper;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('SYN');
        $this->setLicenseType(array('Consumer', 'Customer', 'Supplier'));
        $this->setConfigBase('epicor_comm_enabled_messages/syn_request/');
        //$this->setFrom(date("Y-m-d", strtotime('-1 week')));
        $this->setSyncUrl($this->_getSyncUrl());

    }
    /**
     * Gets the URL for the SYN request
     * 
     * Determines if a http or https url should be sent
     * 
     * @return string
     */
    protected function _getSyncUrl()
    {
        $secure = false;
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->scopeConfig->isSetFlag('web/secure/use_in_frontend', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId())) {
                $secure = true;
            }
        }

        //M1 > M2 Translation Begin (Rule p2-5.3)
        //return Mage::getBaseUrl(\Magento\Store\Model\Store::URL_TYPE_WEB, $secure);
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, $secure);
        //M1 > M2 Translation End
    }

    public function buildRequest()
    {
//--SF  date_default_timezone_set('America/New_York');      
        $this->setMessageSubject(implode(',', array_keys($this->getMessageTypes())));

        $message = $this->_processStores($this->getMessageTemplate());
        $message['messages']['request']['body'] = array_merge($message['messages']['request']['body'], array(
            'synchronize' => array(
                '_attributes' => array(
                    //M1 > M2 Translation Begin (Rule 32)
                    //'from' => $this->getFrom() ? $this->getHelper()->getLocalDate($this->getFrom(), self::DATE_FORMAT) : ''
                    //'from' => $this->getFrom() ? $this->getFrom() : $this->getHelper()->getLocalDate($this->getFrom(), \IntlDateFormatter::LONG),
                    'from' => $this->getFrom() ? $this->getFrom() : '',
                    //M1 > M2 Translation End
                ),
                'hostUrl' => $this->getSyncUrl() . 'eccResponder.php',
                'messages' => array()
            ),
        ));

        $usedLanugages = array();
        $usedMessageTypes = array();

        foreach ($this->getMessageTypes() as $message_type => $languages) {
            $message['messages']['request']['body']['synchronize']['messages']['message'][] = array(
                '_attributes' => array(
                    'type' => $message_type,
                ),
                'languages' => array(
                    'languageCode' => $languages
                )
            );

            $usedMessageTypes[] = $message_type;
            $usedLanugages = array_merge($languages);
        }

        $this->setUsedLanguages(array_unique($usedLanugages));
        $this->setUsedMessageTypes($usedMessageTypes);

        $this->setOutXml($message);
        return true;
    }

    private function _processStores($message)
    {
        $brands = array();
        $brandsAdded = array();
        $websites = $this->getWebsites();
        $stores = $this->getStores();
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        if (!empty($websites)) {
            foreach ($websites as $websiteId) {
                $branding = $helper->getWebsiteBranding($websiteId);
                $brandArray = array(
                    'company' => $branding->getCompany(),
                    'site' => $branding->getite(),
                    'warehouse' => $branding->getWarehouse(),
                    'group' => $branding->getGroup()
                );

                if (!in_array(implode('', $brandArray), $brandsAdded)) {
                    $brands[] = $brandArray;
                    $brandsAdded[] = implode('', $brandArray);
                }
            }
        }

        if (!empty($stores)) {
            foreach ($stores as $storeId) {
                $branding = $helper->getStoreBranding($storeId);
                $brandArray = array(
                    'company' => $branding->getCompany(),
                    'site' => $branding->getSite(),
                    'warehouse' => $branding->getWarehouse(),
                    'group' => $branding->getGroup()
                );

                if (!in_array(implode('', $brandArray), $brandsAdded)) {
                    $brands[] = $brandArray;
                    $brandsAdded[] = implode('', $brandArray);
                }
            }
        }

        unset($message['messages']['request']['body']['brand']);

        if (!empty($brands)) {
            $this->setUsedBrands($brands);
            $message['messages']['request']['body']['brands']['brand'] = $brands;
        } else {
            $message['messages']['request']['body']['brands']['brand'] = array(
                'company' => '',
                'site' => '',
                'warehouse' => '',
                'group' => ''
            );
        }

        return $message;
    }

    public function processResponse()
    {
        if ($this->isSuccessfulStatusCode()) {
            $this->logSyn();
            $this->dirtyEntityReg();
        }

        return $this->isSuccessfulStatusCode();
    }

    /**
     * Logs the SYN request in the SYN Log
     */
    private function logSyn()
    {
        $log = $this->commSynLogFactory->create();
        /* @var $log Epicor_Comm_Model_Syn_log */

        $log->setMessage($this->_xml_out);
        $log->setFromDate($this->getFrom());

        $log->setTypes(serialize($this->getUsedMessageTypes()));
        $log->setBrands(serialize($this->getUsedBrands()));
        $log->setLanguages(serialize($this->getUsedLanguages()));

        if ($this->getTrigger() == 'Admin') {
            $session = $this->backendAuthSession;
            /* @var $user Mage_Admin_Model_Session */
            $user = $session->getUser();
            $log->setIsAuto(false);
            $log->setCreatedById($user->getId());
            $log->setCreatedByName($user->getName());
        } else {
            $log->setIsAuto(true);
            $log->setCreatedByName($this->getTrigger());
        }

        //M1 > M2 Translation Begin (Rule 25)
        //$log->setCreatedAt(now());
        $log->setCreatedAt(date('Y-m-d H:i:s'));
        //M1 > M2 Translation End
        $log->save();
    }

    /**
     * Dirties any entity registrations if it's a full sync or if one of the types is classed as a full sync
     */
    private function dirtyEntityReg()
    {
        $helper = $this->commEntityregHelper;
        /* @var $helper Epicor_Comm_Helper_Entityreg */

        if (!$this->getFrom()) {

            $types = $helper->getRegistryTypes($this->getUsedMessageTypes());
            $helper->dirtyEntityRegistrations($types);
        } else {
            $alwaysFull = explode(',', $this->getConfig('full_sync'));

            if (!empty($alwaysFull)) {
                $dirtyTypes = array();
                $usedTypes = $this->getUsedMessageTypes();
                foreach ($alwaysFull as $type) {
                    if (in_array($type, $usedTypes)) {
                        $dirtyTypes[] = $type;
                    }
                }

                if (!empty($dirtyTypes)) {
                    $types = $helper->getRegistryTypes($dirtyTypes);
                    $helper->dirtyEntityRegistrations($types);
                }
            }
        }
    }

    /**
     * get currenct admin user
     * 
     * @return \Magento\User\Model\User
     */
    protected function getCurrentUser()
    {
        if (!isset($this->_user)) {
            
        }
        return $this->_user;
    }

    /**
     * 
     * Add language to the sync message
     * 
     * @param string|array $language
     * @param string|array $message_type
     */
    public function addLanguage($languages, $messageTypes = 'all')
    {
        if (!is_array($languages))
            $languages = explode(',', $languages);

        $message_types = $this->getMessageTypes();
        if ($messageTypes == 'all') {
            foreach ($message_types as &$type_languages) {
                foreach ($languages as $language) {
                    $language = $this->getHelper()->getLanguageMapping($language);
                    if (!in_array($language, $type_languages))
                        $type_languages[] = $language;
                }
            }
        } else {
            if (!is_array($messageTypes))
                $messageTypes = array($messageTypes);

            foreach ($messageTypes as $messageType) {
                if (isset($message_types[$messageType])) {
                    $type_languages = &$message_types[$messageType];
                    foreach ($languages as $language) {
                        $language = $this->getHelper()->getLanguageMapping($language);
                        if (!in_array($language, $type_languages))
                            $type_languages[] = $language;
                    }
                }
            }
        }
        $this->setMessageTypes($message_types);
    }

    /**
     * Add message type to sync message
     * 
     * @param string|array $messageType
     */
    public function addMessageType($messageTypes)
    {
        if (!is_array($messageTypes))
            $messageTypes = array($messageTypes);

        $message_types = $this->getMessageTypes();
        foreach ($messageTypes as $messageType) {
            if (!isset($message_types[$messageType])) {
                $message_types[$messageType] = array();
                $this->setMessageTypes($message_types);
            }
        }
    }

}
