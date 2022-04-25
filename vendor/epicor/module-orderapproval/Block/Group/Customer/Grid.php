<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Group\Customer;

use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;

class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    private $commHelper;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    private $listsHelper;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    private $listsListModelFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $generic;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    private $backendHelper;

    /**
     * @var \Epicor\OrderApproval\Model\ResourceModel\Groups\CollectionFactory
     */
    private $groupCollectionFactory;

    private $selected;

    /**
     * @var GroupCustomers
     */
    private $groupCustomers;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory
     * @param \Epicor\Common\Helper\Data $commonHelper
     * @param \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper
     * @param \Epicor\Comm\Helper\Data $commHelper
     * @param \Epicor\Lists\Helper\Data $listsHelper
     * @param \Epicor\Lists\Model\ListModelFactory $listsListModelFactory
     * @param \Epicor\OrderApproval\Model\ResourceModel\Groups\CollectionFactory $groupCollectionFactory
     * @param GroupCustomers $groupCustomers
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\OrderApproval\Model\ResourceModel\Groups\CollectionFactory $groupCollectionFactory,
        GroupCustomers $groupCustomers,
        array $data = []
    ) {
        $this->groupCustomers = $groupCustomers;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->storeManager = $context->getStoreManager();
        $this->commHelper = $commHelper;
        $this->listsHelper = $listsHelper;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->generic = $context->getSession();
        $this->backendHelper = $backendHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );
        $this->setId('group_customers');
        $this->setIdColumn('id');
        $this->setSaveParametersInSession(false);
        $this->setMessageBase('epicor_comm');
        $this->setCustomColumns($this->_getColumns());
        $this->setCacheDisabled(true);
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(['selected_customers' => 1]);
    }

    /**
     * @return \Epicor\Common\Block\Generic\Listing\Grid|\Magento\Backend\Block\Widget\Grid|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
        $erpAccount = $this->commHelper->getErpAccountInfo();
        $collection = $erpAccount->getCustomers($erpAccount->getId());

        $collection->addFieldToFilter('website_id', $this->storeManager->getWebsite()->getId());
        $collection->addNameToSelect();
        $this->addEmailFilter($collection);
        $this->setCollection($collection);
        return \Magento\Backend\Block\Widget\Grid::_prepareCollection();
    }

    /**
     * @param $collection
     */
    private function addEmailFilter($collection)
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);
        if (!is_null($filter)) {
            $filter = $this->backendHelper->prepareFilterString($filter);
            if (isset($filter['address_email_address'])) {
                $collection
                    ->addAttributeToFilter('email', array("like" => '%' . $filter['address_email_address'] . '%'));
            }
        }
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml(false);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string|void
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * @return array[]
     */
    protected function _getColumns()
    {
        return array(
            'selected_customers' => array(
                'header_css_class' => 'a-center',
                'index' => 'entity_id',
                'type' => 'checkbox',
                'name' => 'selected_customers',
                'renderer' => '\Epicor\OrderApproval\Block\Group\Customer\Render\Checkbox',
                'values' => $this->getSelected(),
                'align' => 'center',
                'filter_index' => 'main_table.entity_id',
                'sortable' => false,
                'disabled_values' => $this->getDisabledFields(),
                'field_name' => 'customers[]'
            ),
            'name' => array(
                'header' => __('Name'),
                'index' => 'name',
                'type' => 'text',
                'condition' => 'LIKE',
            ),
            'address_email_address' => array(
                'header' => __('Email'),
                'index' => 'email',
                'type' => 'text',
            ),
            'row_id' => array(
                'header' => __('Position'),
                'name' => 'row_id',
                'type' => 'number',
                'validate_class' => 'validate-number',
                'index' => 'entity_id',
                'width' => 0,
                'editable' => true,
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display'
            ),
        );
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getDisabledFields()
    {
        if (!$this->groupCustomers->isEditableByCustomer()) {
            return $this->getAllCustomerIds();
        }
        return [];
    }

    /**
     * @return $this|\Epicor\Common\Block\Generic\Listing\Grid
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $column = $this->getColumn('selected_customers');
        $column->unsetData('header');
        $column->setData('group_id', $this->getGroupId());

        return $this;
    }

    /**
     * @return array
     */
    private function getAllCustomerIds()
    {
        $erpAccount = $this->commHelper->getErpAccountInfo();
        $collection = $erpAccount->getCustomers($erpAccount->getId());
        return array_values($collection->getAllIds());
    }

    /**
     * @return array
     */
    public function getSelected()
    {
        $groupId = $this->getGroupId();
        if (!$this->selected && $groupId) {
            /** @var \Epicor\OrderApproval\Model\ResourceModel\Groups\Collection $collection */
            $collection = $this->groupCollectionFactory->create();
            $collection->getSelect()
                ->reset(\Zend_Db_Select::COLUMNS)
                ->joinLeft(
                    ['cgr' => 'ecc_approval_group_customer'],
                    'cgr.group_id = main_table.group_id',
                    ['cgr.customer_id']
                );
            $collection->addFieldToFilter('cgr.group_id', ['eq' => $groupId]);
            $this->selected = [];
            foreach ($collection as $result) {
                $this->selected[] = $result->getData('customer_id');
            }
        }

        return $this->selected;
    }

    /**
     * @return mixed
     */
    private function getGroupId()
    {
        return $this->_request->getParam('id');
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     *
     * @return Grid|void
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'selected_customers') {
            $this->setCustomerSelectedFilter();
        } else {
            parent::_addColumnFilterToCollection($column);
        }
    }

    /**
     * @return void
     */
    private function setCustomerSelectedFilter()
    {
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
        $collection = $this->getCollection();
        if ($collection && !empty($this->selected)) {
            $collection->addFieldToFilter('entity_id', ['in' => array_values($this->selected)]);
        }
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/customersgrid', array('_current' => true));
    }

}