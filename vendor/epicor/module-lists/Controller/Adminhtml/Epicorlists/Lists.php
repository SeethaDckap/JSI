<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists;

use Epicor\Comm\Controller\Adminhtml\Generic;
use Epicor\Lists\Controller\Adminhtml\Context;
use Magento\Backend\Model\Auth\Session;

/**
 * List admin actions
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
abstract class Lists extends Generic
{
    protected $_currentCustomersInErpAccounts = array();
    protected $_erpaccounts = array();

    /**
     * @var \Epicor\Lists\Model\ContractFactory
     */
    protected $listsContractFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $dateTimeTimezone;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    /**
     * @var \Epicor\Comm\Helper\DataFactory
     */
    protected $commHelperFactory;

    /**
     * @var \Epicor\Lists\Helper\DataFactory
     */
    protected $listsHelperFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;


    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Model\ListModel\RuleFactory
     */
    protected $listsListModelRuleFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     * @since 100.2.0
     */
    protected $serializer;

    /**
     * Lists constructor.
     * @param Context $context
     * @param Session $backendAuthSession
     */
    public function __construct(
        Context $context,
        Session $backendAuthSession
    ) {
        $this->registry = $context->getRegistry();
        $this->listsContractFactory = $context->getListsContractFactory();
        $this->listsListModelFactory = $context->getListsListModelFactory();
        $this->backendJsHelper = $context->getBackendJsHelper();
        $this->commHelperFactory = $context->getCommHelperFactory();
        $this->listsHelperFactory = $context->getListsHelperFactory();
        $this->backendSession = $context->getBackendSession();
        $this->listsListModelRuleFactory = $context->getListsListModelRuleFactory();
        $this->jsonHelper = $context->getJsonHelper();
        $this->dateTimeTimezone = $context->getDateTimeTimezone();
        $this->serializer = $context->getSerializer();
        parent::__construct($context, $backendAuthSession);
    }

    /**
     * Admin ACL method
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->backendAuthSession
                ->isAllowed('Epicor_Lists::lists');
    }
/**
     * saves contract specific fields
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     */
    protected function processContractFieldSave($list, $data)
    {
        if ($list->getTypeInstance()->isContract()) {
            $model = $this->listsContractFactory->create();
            $model->load($list->getId(), 'list_id');
            $model->setListId($list->getId());
            $poNumber = isset($data['po_number']) ? $data['po_number'] : null;
            if ($poNumber) {
                $model->setPurchaseOrderNumber($poNumber);
            }
            $model->save();
        }
    }

    /**
     * Saves details for the list
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     */
    protected function processDetailsSave($list, $data)
    {
        $list->setTitle($data['title']);
        $list->setNotes($data['notes']);
        $this->setCustomerExclusion($list, $data);
        $list->setDescription($data['description']);
        $list->setPriority(isset($data['priority']) ? $data['priority'] : 0);
        $list->setActive(isset($data['active']) ? 1 : 0);

        if ($list->isObjectNew()) {
            $list->setErpCode($data['erp_code']);
            $list->setType($data['type']);
            $label = empty($data['label']) ? $data['title'] : $data['label'];
            $list->setLabel($label);
        } else {
            $this->processErpOverrideSave($list, $data);
        }
        if ($list->getType() != "Co") {
            if (empty($data['start_date']) == false) {
                if (!property_exists($data, 'select_start_time')) {
                    $data['start_time'] = array('00', '00', '00');
                }
                $time = implode(':', $data['start_time']);
                $dateTime = $data['start_date'] . ' ' . $time;

                $list->setStartDate($this->dateTimeTimezone->convertConfigTimeToUtc($dateTime));
            } else {
                $list->setStartDate(false);
            }

            if (empty($data['end_date']) == false) {
                if (!property_exists($data, 'select_end_time')) {
                    $data['end_time'] = array('23', '59', '59');
                }
                $time = implode(':', $data['end_time']);
                $dateTime = $data['end_date'] . ' ' . $time;

                $list->setEndDate($this->dateTimeTimezone->convertConfigTimeToUtc($dateTime));
            } else {
                $list->setEndDate(false);
            }
        }

        $this->processSettingsSave($list, $data);
    }

    private function setCustomerExclusion($list, $data)
    {
        if (!isset($data['customer_exclusion'])) {
            $list->setCustomerExclusion('N');
            return;
        }
        $list->setCustomerExclusion($this->getExclusionFlag($data));
    }

    private function getExclusionFlag($data)
    {
        if ($data['customer_exclusion'] === '1' || $data['customer_exclusion'] === 'Y') {
            return 'Y';
        } else {
            return 'N';
        }
    }

    /**
     * Checks if Settings Information needs to be saved
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     */
    protected function processSettingsSave($list, $data)
    {
        $settings = isset($data['settings']) ? $data['settings'] : array();
        $productVals = isset($data['links']['products']) ? $data['links']['products'] : null;
        $excludeExist = (isset($productVals)) ? 1 : 0;
        //Product tab values are present
        if ($excludeExist) {
            $excludeSelectedProducts = $data['exclude_selected_products'];
            if ($excludeSelectedProducts) {
                $settings[] = 'E';
            }
        } else {
            //Product tab is not loaded
            $exclusion = in_array('E', $list->getSettings()) ? true : false;
            if ($exclusion) {
                $settings[] = 'E';
            }
        }
        $list->setSettings($settings);
    }

    /**
     * Checks if ERP Override Information needs to be saved
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     */
    protected function processErpOverrideSave($list, $data)
    {
        $overrides = isset($data['erp_override']) ? $data['erp_override'] : array();
        $list->setErpOverride($overrides);
    }
/**
     * Checks if Labels Information needs to be saved
     *
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @param array $data
     */
    protected function processLabelsSave($list, $data)
    {
        if (isset($data['label'])) {
            $list->setLabel($data['label']);
        }

        if (isset($data['labels'])) {
            $labels = $data['labels'];
            $this->processWebsiteLabelsSave($list, $labels);
        }
    }

    /**
     * Saves Website Specific label information
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $labels
     */
    protected function processWebsiteLabelsSave($list, $labels)
    {
        foreach ($labels as $websiteId => $webLabel) {
            if (empty($webLabel['default'])) {
                $list->removeWebsiteLabel($websiteId);
            } else {
                $list->addWebsiteLabel($websiteId, $webLabel['default']);
            }

            if (empty($webLabel['groups'])) {
                continue;
            }

            $this->processStoreGroupLabelsSave($list, $webLabel['groups']);
        }
    }

    /**
     * Processes store Group Label information
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $labels
     */
    protected function processStoreGroupLabelsSave($list, $labels)
    {
        foreach ($labels as $groupId => $groupLabel) {
            if (empty($groupLabel['default'])) {
                $list->removeStoreGroupLabel($groupId);
            } else {
                $list->addStoreGroupLabel($groupId, $groupLabel['default']);
            }

            if (empty($groupLabel['stores'])) {
                continue;
            }

            $this->processStoreLabelsSave($list, $groupLabel['stores']);
        }
    }

    /**
     * Processes Store label information
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $labels
     */
    protected function processStoreLabelsSave($list, $labels)
    {
        foreach ($labels as $storeId => $label) {
            if (empty($label)) {
                $list->removeStoreLabel($storeId);
            } else {
                $list->addStoreLabel($storeId, $label);
            }
        }
    }
/**
     * Checks if ERP Accounts Information needs to be saved
     *
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @param array $data
     */
    protected function processERPAccountsSave($list, $data)
    {
        $erpaccounts = $this->getRequest()->getParam('selected_erpaccounts');
        if (!is_null($erpaccounts)) {
            $this->saveERPAccounts($list, $data);
            // if erp_account_link_type = 'N', save erp account exclusion indicator as 'N', else save value
            $linkType = $data['erp_account_link_type'];
            $dataExclusion = isset($data['erp_accounts_exclusion']) ? 'Y' : 'N';
            $exclusion = $linkType == 'N' ? 'N' : $dataExclusion;
            $list->setErpAccountLinkType($linkType);
            $list->setErpAccountsExclusion($exclusion);
        }
    }

    /**
     * Save ERP Accounts Information
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     * @return void
     */
    protected function saveERPAccounts(&$list, $data)
    {
        $erpaccounts = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['erpaccounts']));
        $list->removeErpAccounts($list->getErpAccounts());
        $list->addErpAccounts($erpaccounts);
    }
/**
     * Checks if Websites Information needs to be saved
     *
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @param array $data
     */
    protected function processWebsitesSave($list, $data)
    {
        $websites = $this->getRequest()->getParam('selected_websites');
        if (!is_null($websites)) {
            $this->saveWebsites($list, $data);
        }
    }

    /**
     * Save Websites Information
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     * @return void
     */
    protected function saveWebsites(&$list, $data)
    {
        $websites = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['websites']));
        $list->removeWebsites($list->getWebsites());
        $list->addWebsites($websites);
    }
/**
     * Checks if Stores Information needs to be saved
     *
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @param array $data
     */
    protected function processStoresSave($list, $data)
    {
        $stores = $this->getRequest()->getParam('selected_stores');
        if (!is_null($stores)) {
            $this->saveStores($list, $data);
        }
    }

    /**
     * Save Stores Information
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     * @return void
     */
    protected function saveStores(&$list, $data)
    {
        $stores = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['stores']));
        $list->removeStoreGroups($list->getStoreGroups());
        $list->addStoreGroups($stores);
    }
/**
     * Checks if Customers Information needs to be saved
     *
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @param array $data
     */
    protected function processCustomersSave($list, $data)
    {
        $customers = $this->getRequest()->getParam('selected_customers');
        if (!is_null($customers)) {
            $this->saveCustomers($list, $data);
        }
    }

    /**
     * Save Customers Information
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     * @return void
     */
    protected function saveCustomers(&$list, $data)
    {
        $customers = isset($data['links']['customers']) ? array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['customers'])) : array();
        $list->removeCustomers($list->getCustomers());
        $list->addCustomers($customers);
    }
/**
     * Checks if Products Information needs to be saved
     *
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @param array $data
     */
    protected function processProductsSave($list, $data)
    {
        $products = $this->getRequest()->getParam('selected_products');
        if (!is_null($products)) {
            $this->saveProducts($list, $data);
        }
    }

    /**
     * Save Products Information
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     * @return void
     */
    protected function saveProducts(&$list, $data)
    {
        $helper = $this->commHelperFactory->create();
        /* @var $helper \Epicor\Comm\Helper\Data */

        $products = array_keys($helper->decodeGridSerializedInput($data['links']['products']));

        $list->removeProducts($list->getProducts());
        $list->addProducts($products);
    }

    /**
     * Checks if ProductsPricing Information needs to be saved
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     * @param array $products
     */
    protected function processProductsPricingSave($list, $data)
    {
        if (isset($data['json_pricing'])) {
            $pricing = json_decode($data['json_pricing'], true);
            $list->addPricing($pricing);
        }
    }

    /**
     * Import Product.
     * 
     * @param $list
     *
     * @return bool
     */
    protected function importProducts($list)
    {
        $helper = $this->listsHelperFactory->create();
        /* @var $helper \Epicor\Lists\Helper\Data */

        $failed = false;
        if (!empty($_FILES['import']['tmp_name'])
            && !in_array($_FILES['import']['type'],
                \Epicor\Comm\Helper\Data::CSV_APPLIED_FORMAT)
        ) {
            $message = 'Wrong File Type. Only CSV files are allowed.';
            $this->messageManager->addErrorMessage($message);
            $failed = true;
        } else {
            if (!empty($_FILES['import']['tmp_name'])) {
                $errors = $helper->importCsvProducts(
                    $list,
                    $_FILES['import']['tmp_name']
                );

                $failed = $this->setError($errors);
            }
        }

        return $failed;
    }

    /**
     * Process Error.
     *
     * @param $errors
     *
     * @return bool
     */
    private function setError($errors)
    {
        $failed = false;
        if (isset($errors['errors'])) {
            foreach ($errors['errors'] as $error) {
                $failed = true;
                $this->messageManager->addErrorMessage($error);
            }
        }

        if (isset($errors['warnings'])) {
            foreach ($errors['warnings'] as $error) {
                $this->messageManager->addWarningMessage($error);
            }
        }

        return $failed;
    }

    protected function importAddresses($list)
    {
        $helper = $this->listsHelperFactory->create();
        /* @var $helper \Epicor\Lists\Helper\Data */

        $failed = false;

        if (!empty($_FILES['import']['tmp_name'])) {
            $errors = $helper->importCsvAddresses($list, $_FILES['import']['tmp_name']);
            if (isset($errors['errors'])) {
                foreach ($errors['errors'] as $error) {
                    $failed = true;
                    $this->backendSession->addError($error);
                }
            }

            if (isset($errors['warnings'])) {
                foreach ($errors['warnings'] as $error) {
                    $this->backendSession->addWarning($error);
                }
            }
        }

        return $failed;
    }
/**
     * Checks if Addresses Information needs to be saved
     *
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @param array $data
     */
    protected function processAddressesSave($list, $data)
    {
        $addresses = $this->getRequest()->getParam('selected_addresses');
        if (!is_null($addresses)) {
            $this->saveAddresses($list, $data);
        }
    }

    /**
     * Save Addresses Information
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     * @return void
     */
    protected function saveAddresses(&$list, $data)
    {
        $addresses = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['addresses']));

        // TODO: load current Addresses and check if any need to be removed
        // TODO: loop through passed values and only add new Addresses
    }

    /**
     * Deletes the given List by id
     *
     * @param integer $id
     * @param boolean $mass
     *
     * @return void
     */
    protected function delete($id, $mass = false)
    {
        $model = $this->listsListModelFactory->create();
        /* @var $list \Epicor\Lists\Model\ListModel */

        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                if ($model->delete()) {
                    if (!$mass) {
                        $this->messageManager->addSuccess(__('List deleted'));
                    }
                } else {
                    $this->messageManager->addError(__('Could not delete List ' . $id));
                }
            }
        }
    }
/**
     * Loads List
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    protected function loadEntity()
    {
        $id = $this->getRequest()->getParam('id', null);
        $list = $this->listsListModelFactory->create()->load($id);
        /* @var $list \Epicor\Lists\Model\ListModel */
        $this->registry->register('list', $list);

        return $list;
    }
/**
     * Checks if there are conditions to save
     *      *
     * @param \Epicor\Lists\Model\ListModel $list
     *
     * @param array $data
     */
    protected function processConditionsSave($list, $data)
    {
        $rule = $this->listsListModelRuleFactory->create();
        $excludeExist = (isset($data['links']['products'])) ? 1 : 0;
        //Product tab values are present
        if ($excludeExist) {
            $condition = $this->getRequest()->getParam('rule_is_enabled');
            //If Link products to list conditionally was ticked
            if (isset($condition)) {
                $data = $this->getRequest()->getParam('rule');
                $rule->loadPost($data);
                $list->setConditions($this->serializer->serialize($rule->getConditions()->asArray()));
            } else {
                //If Link products to list conditionally was not ticked
                $list->setConditions(null);
            }
        }
    }
}
