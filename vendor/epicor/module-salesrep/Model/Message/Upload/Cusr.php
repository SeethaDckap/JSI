<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model\Message\Upload;

use Magento\Framework\Math\Random;


/**
 * Response CUSR - Upload Sales Rep Record
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Cusr extends \Epicor\Comm\Model\Message\Upload
{

    protected $_exists = false;

    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */
    protected $salesRepAccountFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;
    /*
     * @var \Magento\Customer\Api\CustomerRepositoryInterfaceFactory $customerRepository
     */
    protected $customerRepository;
    /**
     * @var Random
     */
    protected $mathRandom;
    /*
     *  @var  \Magento\Framework\App\Cache\StateInterface $eavCacheStateInterface
     */
    protected $eavCacheStateInterface;


    public function __construct(
        \Epicor\Comm\Model\Context $context,
       \Magento\Framework\App\Cache\StateInterface $eavCacheStateInterface,     
       \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,     
        \Epicor\SalesRep\Model\AccountFactory $salesRepAccountFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Framework\Math\Random $random,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->mathRandom = $random;
        $this->salesRepAccountFactory = $salesRepAccountFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->customerRepository = $customerRepository;
        $this->eavCacheStateInterface = $eavCacheStateInterface;
        parent::__construct(
            $context,
            $resource,
            $resourceCollection,
            $data
        );
        $this->setConfigBase('epicor_comm_field_mapping/cusr_mapping/');
        $this->setMessageType('CUSR');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_CUSTOMER);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        //Mage::register('entity_register_update_erpaccount', true, true);
        //Mage::register('entity_register_update_erpaddress', true, true);
    }

    public function processAction()
    {
        $erpData = $this->getRequest()->getSalesRep();

        if (!$erpData) {
            throw new \Exception($this->getErrorDescription(\Epicor\Comm\Model\Message::STATUS_XML_TAG_MISSING, 'SalesRep'), \Epicor\Comm\Model\Message::STATUS_XML_TAG_MISSING);
        }

        $salesRepAccount = $this->salesRepAccountFactory->create();
        /* @var $salesRepAccount \Epicor\SalesRep\Model\Account */

        $salesRepId = $erpData->getSalesRepId();
        $salesRepAccountId = $erpData->getSalesRepAccountId() ?: $salesRepId;
        $salesRepAccountName = $erpData->getSalesRepAccountName() ?: $salesRepAccountId;

        $salesRepAccount->load($salesRepAccountId, 'sales_rep_id');

        $this->_exists = !$salesRepAccount->isObjectNew();

        $flags = $erpData->getData('_attributes');

        if ($flags && $flags->getDelete() == 'Y') {
            if (!$salesRepAccount->isObjectNew()) {
                $this->_processContact($salesRepAccount, $erpData, true);
                $this->_processHierarchy($salesRepAccount, $erpData, true);
                $salesRepAccount->delete();
            }
        } else {

            if ($this->isUpdateable('name_update', $this->_exists)) {
                $salesRepAccount->checkAndSetName($salesRepAccountName);
            }

            $salesRepAccount->checkAndSetSalesRepId($salesRepAccountId);
            $salesRepAccount->save();

            $this->_processContact($salesRepAccount, $erpData);

            $brands = $this->getRequest()->getBrands();
            $brand = null;
            if (!is_null($brands)){
                $brand = $brands->getBrand();
            }
            //Modified as multi brand processing is not a requirement for CUSR.
            if (is_array($brand)){
                $brand = $brand[0];
            }
            $this->_processErpAccounts($salesRepAccount, $erpData, $brand);
            $this->_processHierarchy($salesRepAccount, $erpData);
        }
    }

    protected function _processContact(&$salesRepAccount, $erpData, $salesRepDel = false)
    {
        $websites = $this->_getWebsitesForBranding();
        foreach ($websites as $website) {
            $customerObj = $this->_loadCustomer($website, $erpData);
            /* @var $customerObj \Epicor\Comm\Model\Customer */

            if ($customerObj->isObjectNew()) {
                $this->_createCustomer($salesRepAccount, $website, $erpData);
            } else {
                if ($salesRepDel == true) {
                    $customerObj->delete();
                } else {
                    $customerObj->setEccSalesRepId($salesRepAccount->getSalesRepId());
                    $customerObj->setEccSalesRepAccountId($salesRepAccount->getId());
                    $customerObj->save();
                }
            }
        }
    }

    /**
     *
     * @param \Epicor\SalesRep\Model\Account $salesRepAccount
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    protected function _processErpAccounts(&$salesRepAccount, $erpData, $brand)
    {
        if (!$this->isUpdateable('erp_accounts_update', $this->_exists)) {
            return;
        }
        
        if (empty($brand) || !$brand->getCompany()) {
            //M1 > M2 Translation Begin (Rule p2-6.5)
            //$brand = $this->getHelper()->getStoreBranding(Mage::app()->getDefaultStoreView()->getId());
            $brand = $this->getHelper()->getStoreBranding($this->storeManager->getDefaultStoreView()->getId());
            //M1 > M2 Translation End
        }
        
        $company = $brand->getCompany();
        $erpAccounts = $this->_getGroupedData('accounts', 'account', $erpData);
        $_save = false;
        foreach ($erpAccounts as $erpAccountData) {
            $flags = $erpAccountData->getData('_attributes');
            $accountNum = $erpAccountData->getAccountNumber();
            $accountNum = ($accountNum !== "" && !is_null($accountNum)) ? $accountNum : $erpAccountData->getValue();
            if (!empty($company)) {
                $delimiter = $this->getHelper()->getUOMSeparator();
                $accountNum = $company . $delimiter . $accountNum;
            }
            $erpAccount = $this->getErpAccount($accountNum);
            if ($erpAccount->getId()) {
                $_save = true;
                if (!$flags || $flags->getDelete() != 'Y') {
                    $salesRepAccount->addErpAccount($erpAccount->getId());
                } else {
                    $salesRepAccount->removeErpAccount($erpAccount->getId());
                }
            }
        }
        if ($_save) {
            $salesRepAccount->setCompanies(array($company));
            $salesRepAccount->save();
        }
    }

    protected function _processHierarchy(&$salesRepAccount, $erpData, $salesRepDel = false)
    {
        if (!$this->isUpdateable('managers_update', $this->_exists)) {
            return;
        }

        $managers = $this->_getGroupedData('managers', 'manager', $erpData);
        $parentDelete = false;
        $parentRepAccount = null;
        // load current and check if any need to be removed
        // loop through passed values and only update customers who are new
        foreach ($managers as $managerData) {
            $flags = $managerData->getData('_attributes');
            $level = ($flags) ? $flags->getNumber() : 0;
            $parentRepAccount = null;
            $childRepAccount = null;

            $managerId = $managerData->getManagerRepAccountId();
            $accountId = ($managerId !== "" && !is_null($managerId)) ? $managerId : $managerData->getManagerId();
            $managerName = $managerData->getManagerRepAccountName();
            $accountName = ($managerName !== "" && !is_null($managerName)) ? $managerName : $managerData->getName();
            $baseRepAccount = $this->salesRepAccountFactory->create();
            /* @var $baseRepAccount Epicor_SalesRep_Model_Account */

            $baseRepAccount->load($accountId, 'sales_rep_id');

            if ($baseRepAccount->isObjectNew()) {
                $baseRepAccount->setName($accountName);
                $baseRepAccount->setSalesRepId($accountId);
                $baseRepAccount->setIsDummy(1);
                $baseRepAccount->save();
            }

            if (!$parentRepAccount) {
                $parentRepAccount = $baseRepAccount;
                /* @var $parentRepAccount Epicor_SalesRep_Model_Account */
                $parentDelete = false;
                if ($flags && $flags->getDelete() == 'Y') {
                    $parentDelete = true;
                } 
//                if ($salesRepDel == true) {
//                    $parentDelete = (count($parentRepAccount->getChildAccountsIds()) == 1 && count($parentRepAccount->getSalesReps()) == 0 && count($parentRepAccount->getErpAccounts()) == 0) ? true : false;                    
//                } 
            } else {
                $childRepAccount = $baseRepAccount;
                /* @var $childRepAccount Epicor_SalesRep_Model_Account */
            }

            if ($parentRepAccount && $childRepAccount) {
                if ($parentDelete) {
                    $parentRepAccount->removeChildAccount($childRepAccount->getId());
                } else {
                    $parentRepAccount->addChildAccount($childRepAccount->getId());
                }

                $parentRepAccount->save();
//                if ($parentDelete) {
//                    // After Save removes heirarchy
//                    $parentRepAccount->delete();
//                }
                $parentRepAccount = $childRepAccount;
                $childRepAccount = null;

                $parentDelete = ($flags && $flags->getDelete() == 'Y') ? true : false;
            }
        } 

        if ($parentRepAccount) {
            if ($parentDelete) {
                $parentRepAccount->removeChildAccount($salesRepAccount->getId());
            } else {
                $parentRepAccount->addChildAccount($salesRepAccount->getId());
            }
            $parentRepAccount->save();
//            if ($parentDelete) {
//                // After Save removes heirarchy
//                $parentRepAccount->delete();
//            }
        }
    }

    /**
     * Loads a Customer for the given data
     *
     * @param integer $website
     * @param \Epicor\Common\Model\Xmlvarien $data
     *
     * @return \Epicor\Comm\Model\Customer
     */
    protected function _loadCustomer($website, $data)
    {
        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
        $collection->addAttributeToFilter('email', $data->getEmailAddress());
        $collection->setFlag('is_salesrep', true);
        $collection->addAttributeToFilter('ecc_contact_code', $data->getContactCode());
        $collection->setFlag('is_salesrep', false);
        $collection->addFieldToFilter('website_id', $website);
        return $collection->getFirstItem();
    }

    protected function _createCustomer($salesRepAccount, $website, $data)
    {
        $customer = $this->customerCustomerFactory->create();
        /* @var $customer \Epicor\Comm\Model\Customer */
        
        $contactCode = $data->getContactCode();
        $name = $data->getName();
        $function = $data->getFunction();
        $telephoneNumber = $data->getTelephoneNumber();
        $faxNumber = $data->getFaxNumber();
        $mobileNumber = $data->getMobileNumber();
        $emailAddress = $data->getEmailAddress();
        $loginId = $data->getLoginId();
        $nameParts = explode(' ', $name, 3);
        $customer->setFirstname($nameParts[0]);
        
        $customer->setWebsiteId($website);
        $store = $this->storeManager->getWebsite($website)->getDefaultStore();
        $customer->setStore($store);
        
          if (count($nameParts) == 3) {
            $customer->setMiddlename($nameParts[1]);
            $customer->setLastname($nameParts[2]);
          } else {
                $customer->setLastname($nameParts[1]);
          }
        $customer->setEmail($emailAddress);
        $customer->setPassword($customer->hashPassword($this->mathRandom->getRandomString(10))); 

        $customer->save();

        if ($customer->getId()) {
            $customer_object = $this->customerRepository->getById($customer->getId());
            $customer_object->setCustomAttribute("ecc_function", $function);
            $customer_object->setCustomAttribute("ecc_telephone_number", $telephoneNumber);
            $customer_object->setCustomAttribute("ecc_fax_number", $faxNumber);
            $customer_object->setCustomAttribute("ecc_mobile_number", $mobileNumber);
            $customer_object->setCustomAttribute("ecc_email", $emailAddress);
            $customer_object->setCustomAttribute("ecc_erp_loginId", $loginId);
            $customer_object->setCustomAttribute("ecc_contact_code", $contactCode);
            $customer_object->setCustomAttribute('ecc_erp_account_type', 'salesrep');
            $customer_object->setCustomAttribute("ecc_sales_rep_id", $salesRepAccount->getSalesRepId());
            $customer_object->setCustomAttribute("ecc_sales_rep_account_id", $salesRepAccount->getId());
            $this->customerRepository->save($customer_object);
        }

        if ($this->getConfigFlag('send_emails')) {
            $customer->sendNewAccountEmail();
        }
    }

}
