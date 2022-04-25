<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Block\Adminhtml\Roles\Edit\Tab;


/**
 * Role Customers Serialized Grid
 *
 * @category   Epicor
 * @package    Epicor_Roles
 * @author     Epicor Websales Team
 */
class Customers extends \Magento\Backend\Block\Widget\Grid\Extended  implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    private $linkTypes = array(
        'B' => 'B2B',
        'C' => 'B2C',
        'R' => 'Dealer',
        'D' => 'Distributor',
        'S' => 'Supplier'
    );

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\AccessRight\Model\RoleModelFactory
     */
    protected $roleModelFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Epicor\Common\Helper\Account\Selector
     */
    protected $commonAccountSelectorHelper;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Epicor\AccessRight\Model\ResourceModel\RoleModel\Erp\Account\CollectionFactory
     */
    protected $erpAccountCollectionFactory;

    /**
     * @var \Epicor\AccessRight\Model\RoleModel|null
     */
    protected $role;

    /**
     * Customers constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\AccessRight\Model\RoleModelFactory $roleModelFactory
     * @param \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Epicor\Common\Helper\Account\Selector $commonAccountSelectorHelper
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Epicor\AccessRight\Model\ResourceModel\RoleModel\Erp\Account\CollectionFactory $erpAccountCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\AccessRight\Model\RoleModelFactory $roleModelFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Epicor\Common\Helper\Account\Selector $commonAccountSelectorHelper,
        \Magento\Store\Model\System\Store $systemStore,
        \Epicor\AccessRight\Model\ResourceModel\RoleModel\Erp\Account\CollectionFactory $erpAccountCollectionFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->roleModelFactory = $roleModelFactory;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->commonAccountSelectorHelper = $commonAccountSelectorHelper;
        $this->systemStore = $systemStore;
        $this->erpAccountCollectionFactory = $erpAccountCollectionFactory;

        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('customersGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(array('selected_customers' => 1));
        $jsObjectName = $this->getJsObjectName();
        $this->setAdditionalJavaScript("
            $jsObjectName.eccReload = $jsObjectName.reload;
            $jsObjectName.reload = 
            function(url) {
                if ($('erp_account_link_type') && typeof erpaccountsGridJsObject !== 'undefined') {
                    $('customersGrid_table').style.display='none';
                    this.reloadParams.erp_account_link_type = $('erp_account_link_type').value;
                    this.reloadParams.erp_accounts_exclusion = $('erp_accounts_exclusion').checked ? 'Y' : 'N';
                    this.reloadParams['erp_accounts[]'] = erpaccountsGridJsObject.reloadParams['erpaccounts[]'];
                }
                this.eccReload(url);
            }
                       
            Event.observe('form_tabs_customers', 'click', function (event) {
                var el = Event.element(event);
                el = el.up('a');
                
                if ($('erp_account_link_type')) {
                    var link_type = $('erp_account_link_type').value;
                    var exclusion = $('erp_accounts_exclusion').checked ? 'Y' : 'N';
                    var erp_accounts = erpaccountsGridJsObject.reloadParams['erpaccounts[]'];
                    if (
                        erpAccountsReloadParams.erp_account_link_type != link_type ||
                        erpAccountsReloadParams.erp_accounts != erp_accounts ||
                        erpAccountsReloadParams.erp_accounts_exclusion != exclusion
                    ) {
                        customersGridJsObject.reload();
                    }
                    erpAccountsReloadParams.erp_account_link_type = link_type;
                    erpAccountsReloadParams.erp_accounts_exclusion = exclusion;
                    erpAccountsReloadParams['erp_accounts[]'] = erp_accounts;
                }
            });
            
            // Import Customer by CSV
            initCustomer({
                table: 'customersGrid',
                tableErp: 'erpaccountsGrid',
                translations: {
                    'No records found.': '" . htmlentities(__('No records found.')) . "',
                    'Please choose a file.': '" . htmlentities(__('Please choose a file.')) . "',
                },
                importUrl: '" . $this->getUrl('*/*/import',
                array('id' => $this->getRole()->getId(), 'type' => 'customer')) . "',
                csvDowloadUrl: '" . $this->getUrl('*/*/export', array('type' => 'customer')) . "'
            });
        ");
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'selected_customers') {
            $ids = $this->_getSelected();
            if (empty($ids)) {
                $ids = array(0);
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $ids));
            } else {
                if ($ids) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $ids));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Is this tab shown?
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab Label
     *
     * @return boolean
     */
    public function getTabLabel()
    {
        return 'Customers';
    }

    /**
     * Tab Title
     *
     * @return boolean
     */
    public function getTabTitle()
    {
        return 'Customers';
    }

    /**
     * Is this tab hidden?
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Gets the Role for customer tab
     *
     * @return \Epicor\AccessRight\Model\RoleModel|mixed|null
     */
    public function getRole()
    {
        if (!isset($this->role)) {
            if ($this->registry->registry('role')) {
                $this->role = $this->registry->registry('role');
            } else {
                $this->role = $this->roleModelFactory->create()->load($this->getRequest()->getParam('id'));
            }
        }
        return $this->role;
    }

    /**
     * Build data for Role Customers
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        $roleId = $this->getRequest()->getParam('id');

        $erpLinkType = $this->getRequest()->getParam('erp_account_link_type');
        $exclusion = $this->getRequest()->getParam('erp_accounts_exclusion');
        $erpAccountIds = $this->getRequest()->getParam('erp_accounts');

        if (!$erpLinkType) {
            $erpAccountIds = $this->_getSeletedErpId($roleId);
            $erpLinkType = $this->getRole()->getErpAccountLinkType()?: 'N';
            $exclusion = $this->getRole()->getErpAccountsExclusion()?: 'N';
        }

        $allowedCustomerTypes = array('salesrep', 'customer');
        $typeNull = false;
        $types = false;
        if (($erpLinkType == "N")) {
            // When the link type of the Role is “No specific link” then the customers tab should show “guests”
            array_push($allowedCustomerTypes, 'guest');
            array_push($allowedCustomerTypes, 'supplier');
        } else if ($erpLinkType == "B") {
            $types = array('B2B');
        }else if ($erpLinkType == "R") {
            $types = array('Dealer');
        }else if ($erpLinkType == "D") {
            $types = array('Distributor');
        }else if ($erpLinkType == "S") {
           // $types = array('supplier');
            if (($key = array_search("customer", $allowedCustomerTypes)) !== false) {
                unset($allowedCustomerTypes[$key]);
            }
            array_push($allowedCustomerTypes, 'supplier');
        } else if ($erpLinkType == "C") {
            $types = array('B2C');
            if ($exclusion == 'Y' && empty($erpAccountIds)) {
                array_push($allowedCustomerTypes, 'guest');
                $typeNull = true;
            }
        } elseif($erpLinkType == "E"){
            array_push($allowedCustomerTypes, 'supplier');
        }

        $erpAccountIds = $this->getValidErpAccountIds($erpAccountIds, $erpLinkType, $exclusion);

        $collection = $this->customerCollectionFactory->create();
        /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */

        $erpaccountTable = $collection->getTable('ecc_erp_account');
        $salesRepTable = $collection->getTable('ecc_salesrep_account');
        $customerErpLinkTable = $collection->getTable('ecc_customer_erp_account');
        $collection->addNameToSelect();

        $collection->addAttributeToSelect('email');
        $collection->addAttributeToSelect('ecc_erpaccount_id', 'left');
        $collection->addAttributeToSelect('ecc_erp_account_type', 'left');
        $collection->addAttributeToSelect('ecc_sales_rep_account_id', 'left');
        $collection->addAttributeToSelect('ecc_supplier_erpaccount_id', 'left');
        $collection->addAttributeToSelect('ecc_master_shopper');

        $collection->joinTable(array('erp' => $customerErpLinkTable), 'customer_id=entity_id', array('erp_link' => 'erp_account_id', 'erp_contact_code' => 'contact_code'), null, 'left');
        $collection->addAttributeToSelect('erp_link');
        $collection->addAttributeToSelect('erp_contact_code');

        $collection->joinTable(array('cc' => $erpaccountTable), 'entity_id=erp_link', array(
            'customer_erp_code' => 'erp_code',
            'customer_company' => 'company',
            'customer_short_code' => 'short_code',
            'account_type' => 'account_type'
        ), null, 'left');

        $collection->joinTable(array('ss' => $erpaccountTable), 'entity_id=ecc_supplier_erpaccount_id', array(
            'supplier_erp_code' => 'erp_code',
            'supplier_company' => 'company',
            'supplier_short_code' => 'short_code'
        ), null, 'left');

        $collection->joinTable(array('sr' => $salesRepTable), 'id=ecc_sales_rep_account_id',
            array('sales_rep_id' => 'sales_rep_id'), null, 'left');

        $collection->addExpressionAttributeToSelect('account_type_all',
            " IF(`erp`.`erp_account_id` != '0', cc.account_type, IF(`at_ecc_supplier_erpaccount_id`.`value` != 0, 'Supplier',IF(`at_ecc_sales_rep_account_id`.`value` != 0, 'Sales Rep', '')))",
            'ecc_erp_account_type');

        $collection->addExpressionAttributeToSelect('erp_account_type',
            "IF(cc.erp_code IS NOT NULL, 'Customer', IF(ss.erp_code IS NOT NULL, 'Supplier', IF(at_ecc_sales_rep_account_id.value IS NOT NULL, 'Sales Rep', 'Guest')))",
            'ecc_erpaccount_id');


        $collection->addExpressionAttributeToSelect('joined_short_code',
            "IF(cc.short_code IS NOT NULL, cc.short_code, IF(ss.short_code IS NOT NULL, ss.short_code, ''))",
            'ecc_erpaccount_id');

        $collection->addAttributeToFilter('ecc_erp_account_type', array('in' => $allowedCustomerTypes));

        if ($erpLinkType != 'N' && $typeNull == false) {

            if(count($erpAccountIds) > 0) {
                $collection->getSelect()->where('(`erp`.`erp_account_id` IN (?) OR `at_ecc_sales_rep_account_id`.`value` != 0 OR `at_ecc_supplier_erpaccount_id`.`value` IN (?))',$erpAccountIds );
            } else {
                $filters = array(
                    array('attribute' => 'ecc_sales_rep_account_id', 'neq' => '0')
                );
                $collection->addFieldToFilter($filters);
            }

        }

        /**
         * If ERP Link Type is B2B it should only Role B2B customers and sales reps.
         * (Similarly if Link Type is B2C - it should only Role B2C customers and sales reps)
         */
        if ($types) {
            $filters = array(
                array('attribute' => 'account_type', $types),
                array('attribute' => 'ecc_sales_rep_account_id', 'neq' => '0')
            );

            if ($typeNull) {
                $filters[] = array('attribute' => 'account_type', array('null' => true));
            }
            $collection->addFieldToFilter($filters);
        }
        $collection->getSelect()->group('e.entity_id');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    /**
     * Gets an array of valid erp account ids based
     * on the flags of the Role and the ids passed
     *
     * @param array $erpAccountIds
     * @param string $erpLinkType
     * @param string $exclusion
     * @return array
     */
    protected function getValidErpAccountIds($erpAccountIds, $erpLinkType, $exclusion)
    {
        if ($erpLinkType == 'N') {
            return array();
        }

        $erpAccountsCollection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $erpAccountsCollection Epicor_Comm_Model_Resource_Customer_Erpaccount_Collection */
        $type = false;
        switch ($erpLinkType) {
            case "B":
            case "C":
            case "R":
            case "D":
            case "S":
                $type = $this->linkTypes[$erpLinkType];
                break;
        }
        if ($type) {
            $erpAccountsCollection->addFieldToFilter('account_type', $type);
        }

        $condition = $exclusion == 'Y' ? 'nin' : 'in';
        $erpAccountIdFilter = empty($erpAccountIds) ? array(0) : $erpAccountIds;
        $erpAccountsCollection->addFieldToFilter('entity_id', array($condition => $erpAccountIdFilter));

        return $erpAccountsCollection->getAllIds();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('selected_customers', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'selected_customers',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'index' => 'entity_id',
            'filter_index' => 'main_table.entity_id',
            'sortable' => false,
            'field_name' => 'links[]'
        ));

        $this->addColumn(
            'customer_short_code', array(
            'header' => __('Short Code'),
            'index' => 'customer_short_code',
            'type' => 'text'
            )
        );
        $this->getColumn('customer_short_code')->setIndex('joined_short_code');

        $this->addColumn('ecc_erp_account_type', array(
            'header' => __('Customer Type'),
            'index' => 'erp_account_type',
            'filter_index' => 'ecc_erp_account_type',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\Accounttype',
            'type' => 'options',
            'width' => '100px',
            'options' => $this->commonAccountSelectorHelper->getAccountTypeNames()
        ));

        $this->addColumn('account_type_all', array(
            'header' => __('ERP Account Type'),
            'index' => 'account_type_all',
            'filter_index' => 'account_type_all',
            'type' => 'options',
            'width' => '100px',
            'options' => $this->getAccountSelector()
        ));

        $this->addColumn(
            'customer_name', array(
            'header' => __('Customer'),
            'index' => 'name',
            'type' => 'text'
            )
        );

        $this->addColumn(
            'email', array(
            'header' => __('Email'),
            'index' => 'email',
            'type' => 'text'
            )
        );

        $this->addColumn('ecc_master_shopper', array(
            'header' => __('Master Shopper'),
            'width' => '50',
            'index' => 'ecc_master_shopper',
            'align' => 'center',
            'type' => 'options',
            'options' => array('1' => 'Yes', '0' => 'No')
        ));

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header' => __('Website'),
                'align' => 'center',
                'width' => '150',
                'type' => 'options',
                'options' => $this->systemStore->getWebsiteOptionHash(true),
                'index' => 'website_id',
            ));
        }

        $this->addColumn('row_id', array(
            'header' => __('Position'),
            'name' => 'row_id',
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'entity_id',
            'width' => 0,
            'editable' => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Used in grid to return selected Customers values.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getSelected()
    {
        return array_keys($this->getSelected());
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $role = $this->getRole();
            /* @var $role \Epicor\AccessRight\Model\RoleModel */

            foreach ($role->getCustomers() as $customer) {
                $this->_selected[$customer->getId()] = array('id' => $customer->getId());
            }
        }
        return $this->_selected;
    }

    /**
     * Sets the selected items array
     *
     * @param array $selected
     *
     * @return void
     */
    public function setSelected($selected)
    {
        if (!empty($selected)) {
            foreach ($selected as $id) {
                $this->_selected[$id] = array('id' => $id);
            }
        }
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        $params = array(
            'id' => $this->getRole()->getId(),
        );
        return $this->getUrl('*/*/customersgrid', $params);
    }

    /**
     * Row Click URL
     *
     * @param \Epicor\Comm\Model\Customer $row
     *
     * @return null
     */
    public function getRowUrl($row)
    {
        return null;
    }

    /**
     * @param bool $roleId
     * @return array
     */
    protected function _getSeletedErpId($roleId = false)
    {
        $erpAccountCollection = $this->erpAccountCollectionFactory->create();
        /* @var $erpAccountCollection \Epicor\AccessRight\Model\ResourceModel\RoleModel\Erp\Account\Collection */
        $erpAccountCollection->addFieldtoFilter('access_role_id', array('in' => $roleId));
        $erpAccountId = array('0' => 0);
        if ($erpAccountCollection->count()) {
            $i = 1;
            foreach ($erpAccountCollection->getData() as $account) {
                $erpAccountId[$i] = $account['erp_account_id'];
                $i++;
            }
            return $erpAccountId;
        } else {
            return $erpAccountId;
        }
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getEmptyText()
    {
        return __('No Customers Selected.');
    }

    /**
     * get Account type option guest | B2B | B2C | sales rep | dealer | distributor
     *
     * @return array
     */
    public function getAccountSelector()
    {
        $data = [];
        if(count($this->linkTypes)){
            foreach ($this->linkTypes as $value) {
                $data[$value] = $value;
            }
        }

        return $data;
    }
}
