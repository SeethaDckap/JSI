<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab;


/**
 * List ERP Accounts Serialized Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Erpaccounts extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Lists\Helper\Data $listsHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->backendAuthSession = $backendAuthSession;
        $this->listsHelper = $listsHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('erpaccountsGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);

        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(array('selected_erpaccounts' => 1));
        $this->setRowInitCallback('toggleErpAccountsGrid');
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag

        if ($column->getId() == 'selected_erpaccounts') {
            $ids = $this->_getSelected();

            if (!empty($ids)) {
                if ($column->getFilter()->getValue()) {
                    $this->getCollection()->addFieldToFilter('main_table.entity_id', array('in' => $ids));
                } else {
                    $this->getCollection()->addFieldToFilter('main_table.entity_id', array('nin' => $ids));
                }
            } else {
                if ($column->getFilter()->getValue()) {
                    $this->getCollection()->addFieldToFilter('main_table.entity_id', array('in' => array('')));
                } else {
                    $this->getCollection()->addFieldToFilter('main_table.entity_id', array('nin' => array('')));
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
        return 'ERP Accounts';
    }

    /**
     * Tab Title
     *
     * @return boolean
     */
    public function getTabTitle()
    {
        return 'ERP Accounts';
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
     * @return boolean
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
     * Build data for List ERP Accounts
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Erpaccounts
     */
    protected function _prepareCollection()
    {
        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Customer_Erpaccount_Collection */
        $selectedType = $this->backendAuthSession->getLinkTypeValue();

        $type = false;
        if ($selectedType == "B") {
            $type = "B2B";
        } elseif ($selectedType == "C") {
            $type = "B2C";
        }

        if ($type) {
            $collection->addFieldToFilter('account_type', array('eq' => $type));
        } else {
            $collection->addFieldToFilter('account_type', array('neq' => 'Supplier'));
        }

        if (!$this->getList()->getTypeInstance()->isSectionEditable('erpaccounts')) {
            $ids = $this->_getSelected();
            if (!empty($ids)) {
                $collection->addFieldToFilter('main_table.entity_id', array('in' => $ids));
            }
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Build columns for List ERP Accounts
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Erpaccounts
     */
    protected function _prepareColumns()
    {
        $helper = $this->listsHelper;
        /* @var $helper Epicor_Lists_Helper_Data */

        $isEditable = $this->getList()->getTypeInstance()->isSectionEditable('erpaccounts');
        $this->addColumn('selected_erpaccounts', array(
            'column_css_class' => $isEditable ? '' : 'no-display',
            'header_css_class' => $isEditable ? 'a-center' : 'no-display',
            'type' => 'checkbox',
            'name' => 'selected_erpaccounts',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'index' => 'entity_id',
            'filter_index' => 'main_table.entity_id',
            'sortable' => false,
            'field_name' => 'links[]'
        ));


        $this->addColumn(
            'account_number', array(
            'header' => __('ERP Account Number'),
            'index' => 'account_number',
            'type' => 'text'
            )
        );

        $this->addColumn('short_code', array(
            'header' => __('Short Code'),
            'index' => 'short_code',
            'filter_index' => 'short_code'
        ));


        $this->addColumn(
            'erp_account_name', array(
            'header' => __('Name'),
            'index' => 'name',
            'type' => 'text'
            )
        );


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
     * Used in grid to return selected ERP Accounts values.
     * 
     * @return array
     */
    protected function _getSelected()
    {
        return array_keys($this->getSelected());
    }

    /**
     * Builds the array of selected ERP Accounts
     * 
     * @return array
     */
    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $list = $this->getList();
            /* @var $list Epicor_Lists_Model_ListModel */

            foreach ($list->getErpAccounts() as $erpAccount) {
                $this->_selected[$erpAccount->getId()] = array('id' => $erpAccount->getId());
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
            '_current' => true,
        );
        return $this->getUrl('epicor_lists/epicorlists_lists/erpaccountsgrid', $params);
    }

    /**
     * Row Click URL
     *
     * @param \Epicor\Comm\Model\Customer\Erpaccount $row
     * 
     * @return null
     */
    public function getRowUrl($row)
    {
        return null;
    }

    public function getEmptyText()
    {
        //M1 > M2 Translation Begin (Rule 55)
        //return $this->__('No ERP Accounts Selected');
        return __('No ERP Accounts Selected');
        //M1 > M2 Translation End
    }

}
