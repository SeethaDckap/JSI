<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab;


/**
 * List Customers Serialized Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Customers extends \Magento\Backend\Block\Widget\Grid\Extended  implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Epicor\Common\Helper\Account\Selector
     */
    protected $commonAccountSelectorHelper;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Erp\Account\CollectionFactory
     */
    protected $listsResourceListModelErpAccountCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\TypeFactory
     */
    protected $listsListModelTypeFactory;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\AccounttypeFactory
     */
    protected $commAdminhtmlCustomerGridRendererAccounttypeFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Common\Helper\Account\Selector $commonAccountSelectorHelper,
        \Magento\Store\Model\System\Store $storeSystemStore,
        \Epicor\Lists\Model\ResourceModel\ListModel\Erp\Account\CollectionFactory $listsResourceListModelErpAccountCollectionFactory,
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        \Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\AccounttypeFactory $commAdminhtmlCustomerGridRendererAccounttypeFactory,
        array $data = []
    )
    {
        $this->commAdminhtmlCustomerGridRendererAccounttypeFactory = $commAdminhtmlCustomerGridRendererAccounttypeFactory;
        $this->registry = $registry;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->listsHelper = $listsHelper;
        $this->commonAccountSelectorHelper = $commonAccountSelectorHelper;
        $this->storeSystemStore = $storeSystemStore;
        $this->listsResourceListModelErpAccountCollectionFactory = $listsResourceListModelErpAccountCollectionFactory;
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
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
            $jsObjectName.reload = function(url) {
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
            } else if ($ids) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $ids));
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
     * Gets the List for this tab
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getList()
    {
        if (!isset($this->list)) {
            if ($this->registry->registry('list')) {
                $this->list = $this->registry->registry('list');
            } else {
                $this->list = $this->listsListModelFactory->create()->load($this->getRequest()->getParam('id'));
            }
        }
        return $this->list;
    }

    /**
     * Build data for List Customers
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Customers
     */
    protected function _prepareCollection()
    {
        $listId = $this->getRequest()->getParam('id');

        $erpLinkType = $this->getRequest()->getParam('erp_account_link_type');
        $exclusion = $this->getRequest()->getParam('erp_accounts_exclusion');
        $erpAccountIds = $this->getRequest()->getParam('erp_accounts');

        if (!$erpLinkType) {
            $erpAccountIds = $this->_getSeletedErpId($listId);
            $erpLinkType = $this->getList()->getErpAccountLinkType();
            $exclusion = $this->getList()->getErpAccountsExclusion();
        }

        $allowedCustomerTypes = array('salesrep', 'customer');
        $typeNull = false;
        $types = false;
        if (($erpLinkType == "N")) {
            // When the link type of the list is “No specific link” then the customers tab should show “guests”
            array_push($allowedCustomerTypes, 'guest');
        } else if ($erpLinkType == "B") {
            $types = array('B2B');
        } else if ($erpLinkType == "C") {
            $types = array('B2C');
            if ($exclusion == 'Y' && empty($erpAccountIds)) {
                array_push($allowedCustomerTypes, 'guest');
                $typeNull = true;
            }
        }

        $erpAccountIds = $this->getValidErpAccountIds($erpAccountIds, $erpLinkType, $exclusion);

        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
        /* @var $collection Mage_Customer_Model_Resource_Customer_Collection */
        $erpaccountTable = $collection->getTable('ecc_erp_account');
        $listTable = $collection->getTable('ecc_list_erp_account');
        $salesRepTable = $collection->getTable('ecc_salesrep_account');
        $customerErpLinkTable = $collection->getTable('ecc_customer_erp_account');
        $collection->addNameToSelect();
        $collection->addAttributeToSelect('email');
        $collection->addAttributeToSelect('ecc_erpaccount_id', 'left');
        $collection->addAttributeToSelect('ecc_erp_account_type', 'left');
        $collection->addAttributeToSelect('ecc_sales_rep_account_id', 'left');
        $collection->addAttributeToSelect('ecc_master_shopper');
        $collection->joinTable(array('erp' => $customerErpLinkTable), 'customer_id=entity_id', array('erp_link' => 'erp_account_id', 'erp_contact_code' => 'contact_code'), null, 'left');
        $collection->addAttributeToSelect('erp_link');
        $collection->joinTable(array('cc' => $erpaccountTable), 'entity_id=erp_link', array('customer_erp_code' => 'erp_code', 'customer_company' => 'company', 'customer_short_code' => 'short_code', 'account_type' => 'account_type'), null, 'left');
        $collection->joinTable(array('sr' => $salesRepTable), 'id=ecc_sales_rep_account_id', array('sales_rep_id' => 'sales_rep_id'), null, 'left');
        $collection->addExpressionAttributeToSelect('joined_short_code',
            "IF(sr.sales_rep_id IS NOT NULL, sr.sales_rep_id, IF(cc.short_code IS NOT NULL, cc.short_code, IF(cc.short_code IS NOT NULL, cc.short_code, '')))",
            'ecc_erpaccount_id');
        $collection->addExpressionAttributeToSelect('erp_account_type', "IF(cc.erp_code IS NOT NULL, 'Customer', IF(at_ecc_sales_rep_account_id.value IS NOT NULL, 'Sales Rep', 'Guest'))", 'ecc_erpaccount_id');

        $collection->addAttributeToFilter('ecc_erp_account_type', array('in' => $allowedCustomerTypes));

        if ($erpLinkType != 'N' && $typeNull == false) {
            if (empty($erpAccountIds)) {
                $erpAccountIds = array(0);
            }
            $collection->getSelect()->where('(`erp`.`erp_account_id` IN (?) OR `at_ecc_sales_rep_account_id`.`value` != 0)', $erpAccountIds );
            if (!empty($erpAccountId) && $erpLinkType != 'N') {
                $collection->getSelect()->where('erp.erp_account_id IN(?)', $erpAccountId);
            }
        }

        // If ERP Link Type is B2B it should only list B2B customers and sales reps. (Similarly if Link Type is B2C - it should only list B2C customers and sales reps) 
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
     * Gets an array of valid erp account ids based on the flags of the list and the ids passed
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
        if (in_array($erpLinkType, array('B', 'C'))) {
            $erpAccountsCollection->addFieldToFilter('account_type', $erpLinkType == 'B' ? 'B2B' : 'B2C');
        }

        $condition = $exclusion == 'Y' ? 'nin' : 'in';
        $erpAccountIdFilter = empty($erpAccountIds) ? array(0) : $erpAccountIds;
        $erpAccountsCollection->addFieldToFilter('entity_id', array($condition => $erpAccountIdFilter));

        return $erpAccountsCollection->getAllIds();
    }
    
    /**
     * Build columns for List Customers
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Customers
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


        $this->addColumn('ecc_erp_account_type', array(
            'header' => __('Customer Type'),
            'index' => 'erp_account_type',
            'filter_index' => 'ecc_erp_account_type',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\Accounttype',
            'type' => 'options',
            'width' => '100px',
            'options' => $this->commonAccountSelectorHelper->getAccountTypeNames('supplier'),
        ));


        $this->addColumn('account_type', array(
            'header' => __('ERP Account Type'),
            'index' => 'account_type',
            'filter_index' => 'account_type',
            'type' => 'options',
            'width' => '100px',
            'options' => array(
                'B2B' => 'B2B',
                'B2C' => 'B2C',
                '' => 'N/A'
            ))
        );

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

        //M1 > M2 Translation Begin (Rule P2-6.8)
        //if (!Mage::app()->isSingleStoreMode()) {
        if (!$this->_storeManager->isSingleStoreMode()) {
            //M1 > M2 Translation End
            $this->addColumn('website_id', array(
                'header' => __('Website'),
                'align' => 'center',
                'width' => '150',
                'type' => 'options',
                'options' => $this->storeSystemStore->getWebsiteOptionHash(true),
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
     */
    protected function _getSelected()
    {
        return array_keys($this->getSelected());
    }

    /**
     * Builds the array of selected Customers
     * 
     * @return array
     */
    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $list = $this->getList();
            /* @var $list Epicor_Lists_Model_ListModel */

            foreach ($list->getCustomers() as $customer) {
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
            'id' => $this->getList()->getId(),
        );
        return $this->getUrl('epicor_lists/epicorlists_lists/customersgrid', $params);
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

    protected function _getSeletedErpId($listId = false)
    {
        $listsCollection = $this->listsResourceListModelErpAccountCollectionFactory->create();
        /* @var $listsCollection Epicor_Lists_Model_Resource_Listing_Erp_Account_Collection */
        $listsCollection->addFieldtoFilter('list_id', array('in' => $listId));
        $erpAccountId = array('0' => 0);
        if ($listsCollection->count()) {
            $i = 1;
            foreach ($listsCollection->getData() as $lists) {
                $erpAccountId[$i] = $lists['erp_account_id'];
                $i++;
            }
            return $erpAccountId;
        } else {
            return $erpAccountId;
        }
    }

    public function getEmptyText()
    {
        $type = $this->listsListModelTypeFactory->create()->getListLabel($this->getList()->getType());
        //M1 > M2 Translation Begin (Rule 55)
        //return $this->__('No Customers Selected. %s not restricted by Customer', $type);
        return __('No Customers Selected. %1 not restricted by Customer', $type);
        //M1 > M2 Translation End
    }

}
