<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Adminhtml\Groups\Edit\Tab;

use Epicor\OrderApproval\Api\GroupsRepositoryInterface;
use Epicor\OrderApproval\Model\GroupsFactory as GroupsFactory;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Erp\Account\CollectionFactory as ErpCollection;
use Magento\Backend\Block\Template\Context as Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory as ErpCustomerResourceCollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Epicor\Common\Helper\Account\Selector as CommonAccountSelectorHelper;
use Magento\Store\Model\System\Store as SystemStore;
use Epicor\OrderApproval\Model\CustomerManagementFactory as CustomerManagementFactory;

/**
 * Group Customers Serialized Grid
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Websales Team
 */
class Customers extends \Magento\Backend\Block\Widget\Grid\Extended
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var array
     */
    private $linkTypes
        = array(
            'B' => 'B2B',
            'C' => 'B2C',
            'R' => 'Dealer',
            'D' => 'Distributor',
            'S' => 'Supplier',
        );

    /**
     * @var array
     */
    private $selected = array();

    /**
     * @var ErpCustomerResourceCollectionFactory
     */
    private $erpCustomerResourceCollectionFactory;

    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var CommonAccountSelectorHelper
     */
    protected $commonAccountSelectorHelper;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var ErpCollection
     */
    protected $erpCollectionFactory;

    /**
     * @var \Epicor\OrderApproval\Model\Groups
     */
    protected $group;

    /**
     * @var GroupsRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var GroupsFactory
     */
    protected $groupsFactory;

    /**
     * @var CustomerManagementFactory
     */
    protected $customerManagementFactory;

    /**
     * Customers constructor.
     *
     * @param Context                              $context
     * @param BackendHelper                        $backendHelper
     * @param ErpCustomerResourceCollectionFactory $erpCustomerResourceCollectionFactory
     * @param CustomerCollectionFactory            $customerCollectionFactory
     * @param CommonAccountSelectorHelper          $commonAccountSelectorHelper
     * @param SystemStore                          $systemStore
     * @param ErpCollection                        $erpCollectionFactory
     * @param GroupsRepositoryInterface            $groupRepository
     * @param GroupsFactory                        $groupsFactory
     * @param CustomerManagementFactory            $customerManagementFactory
     * @param array                                $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        ErpCustomerResourceCollectionFactory $erpCustomerResourceCollectionFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        CommonAccountSelectorHelper $commonAccountSelectorHelper,
        SystemStore $systemStore,
        ErpCollection $erpCollectionFactory,
        GroupsRepositoryInterface $groupRepository,
        GroupsFactory $groupsFactory,
        CustomerManagementFactory $customerManagementFactory,
        array $data = []
    ) {
        $this->erpCustomerResourceCollectionFactory
            = $erpCustomerResourceCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->commonAccountSelectorHelper = $commonAccountSelectorHelper;
        $this->systemStore = $systemStore;
        $this->erpCollectionFactory = $erpCollectionFactory;
        $this->groupRepository = $groupRepository;
        $this->groupsFactory = $groupsFactory;
        $this->customerManagementFactory = $customerManagementFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('customersGrid');
        //$this->setUseAjax(true);
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
                if (typeof erpaccountsGridJsObject !== 'undefined') {                
                    this.reloadParams['erp_accounts[]'] = erpaccountsGridJsObject.reloadParams['erpaccounts[]'];
                }
                this.eccReload(url);
            }

            Event.observe('tab_customers_tab', 'click', function (event) {           
                var erp_accounts = '';
                if(erpaccountsGridJsObject){
                    erp_accounts = erpaccountsGridJsObject.reloadParams['erpaccounts[]'];
                }
            
                customersGridJsObject.reload();
                erpAccountsReloadParams['erp_accounts[]'] = erp_accounts;
            });
        ");
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     *
     * @return $this|Customers
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'selected_customers') {
            $ids = $this->_getSelected();
            if (empty($ids)) {
                $ids = array(0);
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()
                    ->addFieldToFilter('entity_id', array('in' => $ids));
            } else {
                if ($ids) {
                    $this->getCollection()
                        ->addFieldToFilter('entity_id', array('nin' => $ids));
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
     * @return \Epicor\OrderApproval\Model\Groups
     */
    public function getGroup()
    {
        if ( ! isset($this->group)) {
            $this->group = $this->groupsFactory->create()->load(
                $this->getRequest()->getParam('group_id')
            );
        }

        return $this->group;
    }

    /**
     * Build data for Groups Customers
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        $groupId = $this->getRequest()->getParam('group_id');

        $erpLinkType = "B";
        //$exclusion = $this->getRequest()->getParam('erp_accounts_exclusion');
        $erpAccountIds = $this->getRequest()->getParam('erp_accounts');

        if ( ! $erpAccountIds) {
            $erpAccountIds = $this->_getSelectedErpId($groupId);
            //$erpLinkType = 'N';
            //$exclusion = $this->getGroup()->getErpAccountsExclusion()?: 'N';
        }

        $allowedCustomerTypes = array('customer');
        $typeNull = false;
        $types = false;
        if ($erpLinkType == "B") {
            $types = array('B2B');
        }

        $erpAccountIds = $this->getValidErpAccountIds($erpAccountIds,
            $erpLinkType);

        $collection = $this->customerCollectionFactory->create();
        /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */

        $erpaccountTable = $collection->getTable('ecc_erp_account');

        $customerErpLinkTable
            = $collection->getTable('ecc_customer_erp_account');

        $collection->addNameToSelect();
        $collection->addAttributeToSelect('email');
        $collection->addAttributeToSelect('ecc_erpaccount_id', 'left');
        $collection->addAttributeToSelect('ecc_erp_account_type', 'left');
        $collection->addAttributeToSelect('ecc_master_shopper');

        $collection->joinTable(array('erp' => $customerErpLinkTable),
            'customer_id=entity_id', array(
                'erp_link'         => 'erp_account_id',
                'erp_contact_code' => 'contact_code',
            ), null, 'left');
        $collection->addAttributeToSelect('erp_link');
        $collection->addAttributeToSelect('erp_contact_code');

        $collection->joinTable(array('cc' => $erpaccountTable),
            'entity_id=erp_link', array(
                'customer_erp_code'   => 'erp_code',
                'customer_company'    => 'company',
                'customer_short_code' => 'short_code',
                'account_type'        => 'account_type',
            ), null, 'left');

        $collection->addExpressionAttributeToSelect('account_type_all',
            " IF(`erp`.`erp_account_id` != '0', cc.account_type,  '')",
            'ecc_erp_account_type');

        $collection->addExpressionAttributeToSelect('erp_account_type',
            "IF(cc.erp_code IS NOT NULL, 'Customer', 'Guest')",
            'ecc_erpaccount_id');

        $collection->addExpressionAttributeToSelect('joined_short_code',
            "IF(cc.short_code IS NOT NULL, cc.short_code, '')",
            'ecc_erpaccount_id');

        $collection->addAttributeToFilter('ecc_erp_account_type',
            array('in' => $allowedCustomerTypes));

        if ($erpLinkType != 'N') {
            if (count($erpAccountIds) > 0) {
                $collection->getSelect()
                    ->where('(`erp`.`erp_account_id` IN (?))', $erpAccountIds);
            }
        }

        /**
         * If ERP Link Type is B2B it should only Groups B2B customers and sales reps.
         * (Similarly if Link Type is B2C - it should only Groups B2C customers and sales reps)
         */
        if ($types) {
            $filters = array(
                array('attribute' => 'account_type', $types)
                //array('attribute' => 'ecc_sales_rep_account_id', 'neq' => '0')
            );
            $collection->addFieldToFilter($filters);
        }
        $collection->getSelect()->group('e.entity_id');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }


    /**
     * @param string $erpAccountIds
     * @param string $erpLinkType
     *
     * @return array
     */
    protected function getValidErpAccountIds($erpAccountIds, $erpLinkType)
    {
        if ($erpLinkType == 'N') {
            return array();
        }

        /* @var $erpCustomerCollection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Collection */
        $erpCustomerCollection
            = $this->erpCustomerResourceCollectionFactory->create();

        $type = false;
        switch ($erpLinkType) {
            case "B":
                $type = $this->linkTypes[$erpLinkType];
                break;
        }

        if ($type) {
            $erpCustomerCollection->addFieldToFilter('account_type', $type);
        }

        //$condition = $exclusion == 'Y' ? 'nin' : 'in';
        $erpAccountIdFilter = empty($erpAccountIds) ? array(0) : $erpAccountIds;
        $erpCustomerCollection->addFieldToFilter(
            'entity_id',
            array('in' => $erpAccountIdFilter)
        );

        return $erpCustomerCollection->getAllIds();
    }

    /**
     * @return Customers
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareColumns()
    {
        $this->addColumn('selected_customers', array(
            'header_css_class' => 'a-center',
            'type'             => 'checkbox',
            'name'             => 'selected_customers',
            'values'           => $this->_getSelected(),
            'align'            => 'center',
            'index'            => 'entity_id',
            'filter_index'     => 'main_table.entity_id',
            'sortable'         => false,
            'field_name'       => 'links[]',
        ));

        $this->addColumn(
            'customer_short_code', array(
                'header' => __('Short Code'),
                'index'  => 'customer_short_code',
                'type'   => 'text',
            )
        );
        $this->getColumn('customer_short_code')->setIndex('joined_short_code');

        $this->addColumn('ecc_erp_account_type', array(
            'header'       => __('Customer Type'),
            'index'        => 'erp_account_type',
            'filter_index' => 'ecc_erp_account_type',
            'renderer'     => '\Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\Accounttype',
            'type'         => 'options',
            'width'        => '100px',
            'options'      => ["customer" => "Customer"],
        ));

        $this->addColumn('account_type_all', array(
            'header'       => __('ERP Account Type'),
            'index'        => 'account_type_all',
            'filter_index' => 'account_type_all',
            'type'         => 'options',
            'width'        => '100px',
            'options'      => ["b2b" => "B2B"],
        ));

        $this->addColumn(
            'customer_name', array(
                'header' => __('Customer'),
                'index'  => 'name',
                'type'   => 'text',
            )
        );

        $this->addColumn(
            'email', array(
                'header' => __('Email'),
                'index'  => 'email',
                'type'   => 'text',
            )
        );

        $this->addColumn('ecc_master_shopper', array(
            'header'  => __('Master Shopper'),
            'width'   => '50',
            'index'   => 'ecc_master_shopper',
            'align'   => 'center',
            'type'    => 'options',
            'options' => array('1' => 'Yes', '0' => 'No'),
        ));

        if ( ! $this->_storeManager->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'  => __('Website'),
                'align'   => 'center',
                'width'   => '150',
                'type'    => 'options',
                'options' => $this->systemStore->getWebsiteOptionHash(true),
                'index'   => 'website_id',
            ));
        }

        $this->addColumn('row_id', array(
            'header'           => __('Position'),
            'name'             => 'row_id',
            'type'             => 'number',
            'validate_class'   => 'validate-number',
            'index'            => 'entity_id',
            'width'            => 0,
            'editable'         => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
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
        if (empty($this->selected)
            && $this->getRequest()->getParam('ajax') !== 'true'
        ) {
            /* @var $group \Epicor\OrderApproval\Model\Groups */
            $group = $this->getGroup();

            /** @var \Epicor\OrderApproval\Model\CustomerManagement $customerManagement */
            $customerManagement = $this->customerManagementFactory->create();
            $customers
                = $customerManagement->getCustomers($group->getGroupId());
            foreach ($customers as $customer) {
                $this->selected[$customer->getId()]
                    = array('id' => $customer->getId());
            }
        }

        return $this->selected;
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
        if ( ! empty($selected)) {
            foreach ($selected as $id) {
                $this->selected[$id] = array('id' => $id);
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
            'group_id' => $this->getGroup()->getId(),
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
     * @param bool $groupId
     *
     * @return array
     */
    protected function _getSelectedErpId($groupId = false)
    {
        /** @var  $erpAccountCollection ErpCollection */
        $erpAccountCollection = $this->erpCollectionFactory->create();
        $erpAccountCollection->addFieldtoFilter('group_id',
            array('in' => $groupId));
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
        if (count($this->linkTypes)) {
            foreach ($this->linkTypes as $value) {
                $data[$value] = $value;
            }
        }

        return $data;
    }
}
