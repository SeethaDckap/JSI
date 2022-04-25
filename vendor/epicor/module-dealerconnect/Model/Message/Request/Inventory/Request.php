<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Message\Request\Inventory;


/**
 * DealersConnect Request Message
 * 
 */
class Request extends \Epicor\Comm\Model\Message\Request
{

    protected $_currencies = array();
    protected $_deliveryLines = array();
    protected $_deliveryDetails = array();
    protected $_contacts = array();
    protected $_mergedAttributeSearches = array();
    protected $_searchPacCriteria = array();   
    protected $_searchInPacCriteria = array();
    protected $_mergedSearches = array();
    protected $_grpNumbers = array();
    

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealerHelper;

    /**
     * @var \Epicor\Dealerconnect\Model\DealergroupsFactory
     */
    protected $dealerGroupModelFactory;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Dealerconnect\Helper\Data $dealerHelper,
        \Epicor\Dealerconnect\Model\DealergroupsFactory $dealerGroupModelFactory,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->_localeResolver = $localeResolver;
        $this->dealerHelper = $dealerHelper;
        $this->dealerGroupModelFactory = $dealerGroupModelFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);

        $this->setAccountNumber($this->commHelper->getErpAccountNumber());
        $this->setStore($this->storeManager->getStore()->getId());
        //M1 > M2 Translation Begin (Rule p2-6.4)
        //$this->setLanguageCode($this->getHelper()->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()));
        $this->setLanguageCode($this->customerconnectMessagingHelper->getLanguageMapping($this->_localeResolver->getLocale()));
        //M1 > M2 Translation End

        $this->setLicenseType('Dealer_Portal');
    }

    public function buildRequest()
    {
        if ($this->getAccountNumber()) {
            $this->addDisplayOption('accountNumber', $this->getAccountNumber());         // account number
            $this->addSecondaryAccountNumbers();
            if ($this->_accountNumbers) {
                $this->addDisplayOption('accounts', $this->_accountNumbers);
            }


            $this->addDisplayOption('languageCode', $this->getLanguageCode());

            if ($this->getIsCurrency()) {                                                 // currency code
                $currencies = array(
                    'currency' => $this->_currencies
                );
                $this->addDisplayOption('currencies', $currencies);
            }
            if ($this->getIsContact()) {
                $contacts = $this->_contacts;
                $this->addDisplayOption('contacts', $contacts);
            }
            if ($this->getIsDeliveryAvailability()) {
                $this->addDisplayOption('delivery', $this->_deliveryDetails);
            }

            $data = $this->getMessageTemplate();
            $data['messages']['request']['body'] = array_merge($data['messages']['request']['body'], $this->_displayData);

            $this->setOutXml($data);
            return true;
        } else {
            return 'Missing account number';
        }
    }

    public function processResponse()
    {
        if ($this->getIsSuccessful()) {
            // getVarienDataFromPath converts xml into a varien object, which can be referenced from controller
            $this->setResults($this->getResponse()->getVarienDataFromPath($this->getResultsPath()));
            return true;
        } else {
            return false;
        }
    }

    public function addCurrencyOption($fieldName, $value)
    {
        $this->_currencies[] = array(
            $fieldName => $value
        );
        return $this;
    }

    public function addContacts($delete, $code, $name, $function, $phone, $fax, $email, $login)
    {
        $this->_contacts['contact'][] = array(
            'contactCode' => $code,
            'name' => $name,
            'function' => $function,
            'telephoneNumber' => $phone,
            'faxNumber' => $fax,
            'emailAddress' => $email,
            'loginId' => $login
        );
        return $this;
    }

    public function getHelper()
    {
        if (!$this->_messaging_helper)
            $this->_messaging_helper = $this->customerconnectMessagingHelper;

        return $this->_messaging_helper;
    }
    
    public function addSearchOption($fieldName, $condition, $value,$columnInfo=null)
    {
        $containsPac = isset($columnInfo['pacattributes']) ? $columnInfo['pacattributes'] : null;
        if (!$containsPac) {
            $searchVariable = '_searchCriteria';
            if (strtoupper($condition) == 'IN') {
                $searchVariable = "_searchInCriteria";
                foreach ($value as $arrayValue) {
                    $search_values['inValue'][] = stripslashes($arrayValue);
                }
                $value = $search_values;
            }
            if (strpos($fieldName, 'pac') == false) {
                $this->{$searchVariable}['search'][] = array(
                    'criteria' => $fieldName,
                    'condition' => $condition,
                    'value' => ($searchVariable == '_searchCriteria') ? $this->stripSlashesDeep(stripslashes($value)) : $value
                );             
            }
        } else {
            $searchVariable = '_searchPacCriteria';
            if(isset($columnInfo['datatypejson'])) {
                $jsonVals = json_decode($columnInfo['datatypejson'],true);
                $attributeVal = $jsonVals['pacattribute'];
                $attributeClass = $jsonVals['parentclass'];
                $pacattributeName = $jsonVals['pacattributeName'];
                //echo $jsonVals->getPacAttribute();
            }
            if (strtoupper($condition) == 'IN') {
                $searchVariable = "_searchInPacCriteria";
                foreach ($value as $arrayValue) {
                    $search_values['inValue'][] = stripslashes($arrayValue);
                }
                $value = $search_values;
            }
            if (strpos($fieldName, 'pac') !== false) {
                $pacVals = explode('pac',$fieldName,2);
                $this->{$searchVariable}['search'][] = array(
                    'class' => $attributeClass,
                    'attribute' => $pacattributeName,
                    'condition' => $condition,
                    'value' => ($searchVariable == '_searchPacCriteria') ? stripslashes($value) : $value
                );               
            }            
        }
        return $this;
    }    
    
    
    public function contains_word($str, $word) {
        if (strpos($str, $word) !== false) {
            return true;
        }
        return false;
    }       

    
    public function mergeAttributeSearches()
    {
        if (array_key_exists('search', $this->_searchPacCriteria) && array_key_exists('search', $this->_searchInPacCriteria)) {
            $this->_mergedAttributeSearches['search'] = array_merge($this->_searchPacCriteria['search'], $this->_searchInPacCriteria['search']);
        } elseif (array_key_exists('search', $this->_searchPacCriteria)) {
            $this->_mergedAttributeSearches['search'] = $this->_searchPacCriteria['search'];
        } elseif (array_key_exists('search', $this->_searchInPacCriteria)) {
            $this->_mergedAttributeSearches['search'] = $this->_searchInPacCriteria['search'];
        }
    }     
    
    public function addDeliveryLines($productCode, $quantity)
    {
        $this->_deliveryLines['line'][] = array(
            'productCode' => $productCode,
            'quantity' => $quantity
        );
        return $this;
    }

}