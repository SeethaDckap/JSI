<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Message\Upload;


use Epicor\Common\Model\Xmlvarien;
use Epicor\Lists\Model\ListModel;
use Epicor\Lists\Model\ListModel\CustomerFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Response CUPG - Upload Product Group
 * 
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Cupg extends \Epicor\Lists\Model\Message\Upload\AbstractModel
{

    protected $_brands;
    protected $_companies;
    protected $_listModel;
    protected $_company;
    protected $_accounts;
    protected $_products;
    protected $_productCodes;
    protected $_deleteProductCodes = array();
    protected $_product;
    protected $_delimiter;
    protected $_currentlistId;
    protected $_listDeleteAttribute;
    protected $_stores;
    protected $_erpOverride;
    protected $_listType = 'Product Group';
    protected $catalogResourceModelProductCollectionFactory;

    private $customerCollection;
    private $customerFactory;
    private $customerRepository;
    private $searchCriteriaBuilder;
    private $filterBuilder;
    private $filterGroupBuilder;
    private $customerDataFromErpContactEmails;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Lists\Helper\Messaging\Customer $listsMessagingCustomerHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Customer\CollectionFactory $customerCollection,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        parent::__construct($context, $listsMessagingCustomerHelper, $commResourceCustomerErpaccountCollectionFactory, $listsListModelFactory, $resource, $resourceCollection, $catalogResourceModelProductCollectionFactory, $data);
        $this->setConfigBase('epicor_comm_field_mapping/cupg_mapping/');
        $this->setMessageType('CUPG');
        $this->customerCollection = $customerCollection;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    public function processList()
    {
        parent::processList();
        if (!$this->_abandonUpload) {
            $this->processContacts();
        }

        return $this;
    }

    protected function processListDetails()
    {
        /** @var $list ListModel */
        $list = $this->getList();
        /** @var $erpData Xmlvarien */
        $erpData = $this->getErpData();
        $exists = $this->listExists();

        if (!$exists) {
            $list->setErpCode($erpData->getListCode());
            $list->setType('Pg');
            $list->setSource('erp');
            $list->setErpAccountLinkType('E');
            $list->setLabel($erpData->getListTitle());
        }

        if ($list->getIsDummy() == 1 || $this->isUpdateable('status_update', $exists, 'title')) {
            $list->setActive($erpData->getListStatus() == 'A' ? true : false);
        }

        if ($list->getIsDummy() == 1 || $this->isUpdateable('title_update', $exists, 'title')) {
            $list->setTitle($erpData->getListTitle());
        }

        if ($this->isUpdateable('settings_update', $exists, 'settings')) {
            $accountAttributes = $this->getAccountAttributes($erpData);
            $accountExclude = $accountAttributes ? $accountAttributes->getExclude() : 'N';
            //does this need another updatable check?
            $list->setErpAccountsExclusion($accountExclude);
            $list->setCustomerExclusion($this->getCustomerExclusion($erpData));

            $this->getCustomerDataFromErpContactCode();
            $this->setListSetting($erpData, $list);
        }

        if ($this->isUpdateable('list_description_update', $exists, 'description')) {
            $list->setDescription($erpData->getListDescription());
        }
        return $this;
    }

    private function setListSetting(Xmlvarien $erpData, ListModel $list)
    {
        $settings = $this->getMergedListAndProductExcludeSettings($erpData);

        $list->setSettings($settings);
    }

    private function getErpProductsAttributes(Xmlvarien $erpData)
    {
        $products = $erpData->getData('products');
        if ($products && $products instanceof Xmlvarien) {
            return $products->getData('_attributes');
        }
    }

    private function getErpProductsExcludeFlag(Xmlvarien $erpData)
    {
        $productAttributes = $this->getErpProductsAttributes($erpData);
        if ($productAttributes && $productAttributes instanceof Xmlvarien) {
            return $productAttributes ? $productAttributes->getData('exclude') : 'N';
        }
    }

    private function getMergedListAndProductExcludeSettings(Xmlvarien $erpData)
    {
        $productExclude = $this->getErpProductsExcludeFlag($erpData);
        $settings = $productExclude === 'Y' ? $erpData->getListSettings() . 'E' : $erpData->getListSettings();

        //if null return empty array
        $settings = $settings ? $settings : [];

        return $settings;
    }

    private function processContacts()
    {
        $this->checkAllCustomersExist();
        $list = $this->getList();
        $existingCustomers = $list->getCustomers();
        $customerFromErp = $this->getCustomerDataFromErpContactEmails();
        $updateErpAccounts = $list->getErpAccountsWithChanges();

        $this->isCustomerAlignedToErpAccount($customerFromErp, $updateErpAccounts);

        $list->removeCustomers($existingCustomers);
        $list->addCustomers($customerFromErp);
    }

    private function isCustomerLinkedToSubmittedErpAccounts(Customer $customer, $updateErpAccounts)
    {
        $accountValid = false;
        $customerErpAccountId = $customer->getCustomAttribute('ecc_erpaccount_id')
            ? $customer->getCustomAttribute('ecc_erpaccount_id')->getValue() : false;

        if ($updateErpAccounts && $customerErpAccountId) {
            $accountValid = $this->isErpAccountAlignedToCustomer($updateErpAccounts, $customerErpAccountId, $customer);
        }

        return $accountValid;
    }

    private function isErpAccountAlignedToCustomer($updateErpAccounts, $customerErpAccount, $customer): bool
    {
        $aligned = false;
        foreach ($updateErpAccounts as $erpAccount) {
            if ($erpAccount->getId() == $customerErpAccount) {
                return true;
            }
        }
        $this->_abandonUpload = true;
        $this->_returnMessages[]
            = 'Customer with email '
            . $customer->getEmail() . ' is not aligned to any of the submitted ERP accounts';

        return $aligned;
    }

    private function isCustomerAlignedToErpAccount($customerFromErp, $updateErpAccounts)
    {
        $isAligned = false;
        foreach ($customerFromErp as $customer) {
            $isAligned = $this->isCustomerLinkedToSubmittedErpAccounts($customer, $updateErpAccounts);
        }

        return $isAligned;
    }

    private function getCustomerExclusion($erpData)
    {
        $contacts = $erpData->getContacts();
        $attributes = $this->getContactAttributes();
        if ($contacts && $attributes) {
            return $attributes->getExclude();
        }
    }

    private function getContactAttributes()
    {
        $contacts = $this->getErpContacts();
        if ($contacts) {
            return $contacts->getData('_attributes');
        }
    }

    private function getErpContacts()
    {
        $erpData = $this->getErpData();
        if ($erpData) {
            return $erpData->getContacts();
        }
    }


    private function getErpContactEmails(): array
    {
        $contact = $this->getErpContactData();

        if (is_array($contact)) {
            $cusEmails = $this->getMultipleContactEmails($contact);
        } else {
            $cusEmails = $this->getSingleContactEmail($contact);
        }

        return $cusEmails;
    }

    private function getMultipleContactEmails($contact): array
    {
        $cusEmails = [];
        foreach ($contact as $contactData) {
            $cusEmails[] = $contactData->getEmailAddress();
        }

        return $cusEmails;
    }

    private function getSingleContactEmail($contact): array
    {
        $cusEmails = [];
        if ($contact instanceof Xmlvarien) {
            $cusEmails[] = $contact->getEmailAddress();
        }

        return $cusEmails;
    }

    private function getErpContactData()
    {
        $erpData = $this->getErpData();

        if ($contacts = $erpData->getContacts()) {
            return $contacts->getContact();
        }
    }

    private function checkAllCustomersExist()
    {
        if (!$this->isAllContactsAvailable()) {
            $this->_abandonUpload = true;
            foreach ($this->getInvalidContacts() as $email) {
                $this->validateContactEmail($email);
            }

        }
    }

    private function validateContactEmail($email)
    {
        if (!$this->isValidEmail($email)) {
            $this->_returnMessages[]
                = "Error the following contact  $email is not a valid email";
        } else {
            $this->_returnMessages[]
                = "Error the following contact email $email does not exist";
        }
    }

    private function isValidEmail($email)
    {
        return preg_match("/^[_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $email);
    }

    private function isAllContactsAvailable()
    {
        return sizeof($this->getErpContactEmails()) === sizeof($this->getCustomerDataFromErpContactEmails());
    }

    private function getInvalidContacts()
    {
        $validCustomers = $this->getCustomerDataFromErpContactEmails();
        $suppliedEmails = $this->getErpContactEmails();
        $validCustomerEmails = [];
        foreach ($validCustomers as $customer) {
            $validCustomerEmails[] = $customer->getEmail();
        }

        return array_diff($suppliedEmails, $validCustomerEmails);
    }

    private function getCustomerDataFromErpContactEmails()
    {
        if (!$this->customerDataFromErpContactEmails) {
            $emails = implode(',', $this->getErpContactEmails());
            $emailFilter = $this->filterBuilder->setField('email')->setConditionType('in')->setValue($emails)->create();
            $filterGroup = $this->filterGroupBuilder->addFilter($emailFilter)->create();
            $searchCriteria = $this->searchCriteriaBuilder->setFilterGroups([$filterGroup])->create();

            $this->customerDataFromErpContactEmails = $this->customerRepository->getList($searchCriteria)->getItems();
        }

        return $this->customerDataFromErpContactEmails;
    }
}
