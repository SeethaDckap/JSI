<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message;


/**
 * Base class for supplier connect messages
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 * 
 * @method setAccountNumber()
 * @method getAccountNumber()
 * @method setLanguageCode()
 * @method getLanguageCode()
 * @method setMaxResults()
 * @method getMaxResults()
 * @method setRangeMin()
 * @method getRangeMin()
 */
class Request extends \Epicor\Comm\Model\Message\Request
{

    protected $_requestData = array();
    protected $_currencies = array();
    protected $_oldValues = array();
    protected $_newValues = array();

    /**
     * @var \Epicor\Supplierconnect\Helper\Messaging
     */
    protected $supplierconnectMessagingHelper;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Supplierconnect\Helper\Messaging $supplierconnectMessagingHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->supplierconnectMessagingHelper = $supplierconnectMessagingHelper;
        parent::__construct(
            $context,
            $resource,
            $resourceCollection,
            $data
        );
        $this->setAccountNumber($this->commHelper->getSupplierAccountNumber());
        $this->setStore($this->storeManager->getStore()->getId());
        $this->setLanguageCode($this->getHelper()->getLanguageMapping($localeResolver->getLocale()));
         $this->setLicenseType('Supplier');
    }

    public function buildRequest()
    {
        // see child classes
    }

    public function processResponse()
    {
        // see child classes
    }

    public function getHelper()
    {
        if (!$this->_messaging_helper)
            $this->_messaging_helper = $this->supplierconnectMessagingHelper;

        return $this->_messaging_helper;
    }

    public function addDisplayOption($fieldName, $value)
    {
        if ($fieldName) {
            $this->_requestData[$fieldName] = $value;
        } else {
            foreach ($value as $key2 => $value2) {
                foreach ($value2 as $key3 => $value3) {
                    $this->_requestData[$key3] = $value3;
                }
            }
        }
        return $this;
    }

    public function addCurrencyOption($fieldName, $value)
    {
        $this->_currencies[] = array(
            $fieldName => $value
        );
        return $this;
    }

    /**
     * 
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function setCustomer($customer)
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        $erpAccount = $helper->getErpAccountInfo(null, 'supplier');

        $this->setCustomerGroupId($erpAccount->getId());
    }

    /**
     * Set the customer group id for the message.
     * @param type $customerGroupId
     */
    public function setCustomerGroupId($customerGroupId)
    {
//do the parent method.
        parent::setCustomerGroupId($customerGroupId);
//get account number then set the legacy header.
        $helper = $this->commMessagingHelper->create();
        $accountNumber = $helper->getSupplierAccountNumber($customerGroupId);
        $this->setAccountNumber($accountNumber);
    }

}
