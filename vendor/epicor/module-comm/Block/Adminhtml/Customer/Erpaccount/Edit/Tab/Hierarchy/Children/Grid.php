<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Hierarchy\Children;


class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    protected $_defaultLimit = 10000;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setId('children');
        $this->setDefaultSort('type');
        $this->setDefaultDir('ASC');

        $this->setSaveParametersInSession(false);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('epicor_common');
        $this->setIdColumn('id');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);
        $this->setUseAjax(false);
        $this->setSkipGenerateContent(true);

        $this->setCustomData($this->getCustomData());
    }

    public function getCustomData()
    {
        $erpAccount = $this->registry->registry('customer_erp_account');
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
        return $erpAccount->getChildAccounts();
    }

    protected function _getColumns()
    {
        $columns = array();

        $columns['child_type'] = array(
            'header' => __('Type'),
            'align' => 'left',
            'index' => 'child_type',
            'sortable' => false,
            'renderer' => 'Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Hierarchy\Renderer\Type'
        );

        $columns['account_number'] = array(
            'header' => __('Account Code'),
            'align' => 'left',
            'index' => 'account_number',
            'sortable' => false
        );

        $columns['name'] = array(
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'name',
            'sortable' => false
        );

        $columns['deleted_children'] = array(
            'header' => __('Delete'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'field_name' => 'deleted_children[]',
            'name' => 'deleted_children',
            'values' => array(),
            'align' => 'center',
            'editable' => true,
            'index' => 'child_type_data',
            'sortable' => false
        );


        return $columns;
    }

    public function getRowUrl($row)
    {
        return null;
    }

    public function getEmptyText()
    {
        return __('No Child Accounts');
    }

}
