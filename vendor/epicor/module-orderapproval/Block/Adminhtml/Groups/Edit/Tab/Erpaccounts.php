<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Adminhtml\Groups\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory as ERPCollectionFactory;
use Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Collection as ERPCollection;
use Epicor\OrderApproval\Model\GroupsFactory as GroupsFactory;
use Epicor\OrderApproval\Model\Groups as Groups;
use Epicor\OrderApproval\Model\ErpManagementFactory as ErpManagementFactory;

/**
 * Groups ERP Accounts Serialized Grid
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor ECC Team
 */
class Erpaccounts extends Extended implements TabInterface
{

    /**
     * @var Groups
     */
    private $group = null;

    /**
     * @var array
     */
    private $_selected = array();

    /**
     * @var GroupsFactory
     */
    protected $groupsFactory;

    /**
     * @var ERPCollectionFactory
     */
    protected $erpCollectionFactory;

    /**
     * @var ErpManagementFactory
     */
    private $erpManagementFactory;

    /**
     * Erpaccounts constructor.
     *
     * @param Context              $context
     * @param Data                 $backendHelper
     * @param GroupsFactory        $groupsFactory
     * @param ERPCollectionFactory $erpCollectionFactory
     * @param ErpManagementFactory $erpManagementFactory
     * @param array                $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        GroupsFactory $groupsFactory,
        ERPCollectionFactory $erpCollectionFactory,
        ErpManagementFactory $erpManagementFactory,
        array $data = []
    ) {
        $this->groupsFactory = $groupsFactory;
        $this->erpCollectionFactory = $erpCollectionFactory;
        $this->erpManagementFactory = $erpManagementFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );

        $this->setUseAjax(true);
        $this->setId('erpaccountsGrid');
        $this->setSaveParametersInSession(false);
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(['selected_erpaccounts' => 1]);
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     *
     * @return $this|Erpaccounts
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'selected_erpaccounts') {
            $ids = $this->_getSelected();

            if ( ! empty($ids)) {
                if ($column->getFilter()->getValue()) {
                    $this->getCollection()->addFieldToFilter(
                        'main_table.entity_id', array('in' => $ids)
                    );
                } else {
                    $this->getCollection()->addFieldToFilter(
                        'main_table.entity_id', array('nin' => $ids)
                    );
                }
            } else {
                if ($column->getFilter()->getValue()) {
                    $this->getCollection()->addFieldToFilter(
                        'main_table.entity_id', array('in' => array(''))
                    );
                } else {
                    $this->getCollection()->addFieldToFilter(
                        'main_table.entity_id', array('nin' => array(''))
                    );
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
     * @return string
     */
    public function getTabLabel()
    {
        return 'ERP Accounts';
    }

    /**
     * Tab Title
     *
     * @return string
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
     * Gets the Groups for this tab
     *
     * @return Groups
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
     * Build data for Groups ERP Accounts
     *
     * @return Erpaccounts
     */
    protected function _prepareCollection()
    {
        $collection = $this->erpCollectionFactory->create();
        /* @var $collection ERPCollection */

        $type = "B2B";
        if ($type) {
            $collection->addFieldToFilter('account_type', array('eq' => $type));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Build columns for ERP Accounts Tabs
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $isEditable = true;
        $this->addColumn(
            'selected_erpaccounts', array(
                'column_css_class' => $isEditable ? '' : 'no-display',
                'header_css_class' => $isEditable ? 'a-center' : 'no-display',
                'type'             => 'checkbox',
                'name'             => 'selected_erpaccounts',
                'values'           => $this->_getSelected(),
                'align'            => 'center',
                'index'            => 'entity_id',
                'filter_index'     => 'main_table.entity_id',
                'sortable'         => false,
                'field_name'       => 'links[]'
            )
        );


        $this->addColumn(
            'account_number', array(
                'header' => __('ERP Account Number'),
                'index'  => 'account_number',
                'type'   => 'text'
            )
        );

        $this->addColumn(
            'short_code', array(
                'header'       => __('Short Code'),
                'index'        => 'short_code',
                'filter_index' => 'short_code'
            )
        );

        $this->addColumn(
            'erp_account_name', array(
                'header' => __('Name'),
                'index'  => 'name',
                'type'   => 'text'
            )
        );

        $this->addColumn(
            'row_id', array(
                'header'           => __('Position'),
                'name'             => 'row_id',
                'type'             => 'number',
                'validate_class'   => 'validate-number',
                'index'            => 'entity_id',
                'width'            => 0,
                'editable'         => true,
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display'
            )
        );

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
        if (empty($this->_selected)
            && $this->getRequest()->getParam('ajax') !== 'true'
        ) {
            /* @var $group Groups */
            $group = $this->getGroup();
            /** @var \Epicor\OrderApproval\Model\ErpManagement $erpManagement */
            $erpManagement = $this->erpManagementFactory->create();
            $erpAccounts = $erpManagement->getErpAccounts($group->getGroupId());
            foreach ($erpAccounts as $erpAccount) {
                $this->_selected[$erpAccount->getId()]
                    = array('id' => $erpAccount->getId());
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
        if ( ! empty($selected)) {
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
            'id'       => $this->getGroup()->getId(),
            '_current' => true,
        );
        return $this->getUrl('*/*/erpaccountsgrid', $params);
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getEmptyText()
    {
        return __('No ERP Accounts Selected');
    }

}
