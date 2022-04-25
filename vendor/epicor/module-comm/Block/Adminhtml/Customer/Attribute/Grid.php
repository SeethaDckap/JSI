<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Attribute;


/**
 * 
 * Customer grid for customer selector input
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Epicor\Common\Helper\Account\Selector
     */
    protected $commonAccountSelectorHelper;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\AccounttypeFactory
     */
    protected $commAdminhtmlCustomerGridRendererAccounttypeFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Common\Helper\Account\Selector $commonAccountSelectorHelper,
        \Magento\Store\Model\System\Store $storeSystemStore,
        \Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\AccounttypeFactory $commAdminhtmlCustomerGridRendererAccounttypeFactory,
        array $data = []
    )
    {
        $this->commAdminhtmlCustomerGridRendererAccounttypeFactory = $commAdminhtmlCustomerGridRendererAccounttypeFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->commonAccountSelectorHelper = $commonAccountSelectorHelper;
        $this->storeSystemStore = $storeSystemStore;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('customer_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setRowClickCallback('accountSelector.selectAccount.bind(accountSelector)');
        $this->setRowInitCallback('accountSelector.updateWrapper.bind(accountSelector)');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
        $erpaccountTable = $collection->getTable('ecc_erp_account');
        $salesRepTable = $collection->getTable('ecc_salesrep_account');

        $collection->addNameToSelect();
        $collection->addAttributeToSelect('email');
        $collection->addAttributeToSelect('ecc_master_shopper');
        $collection->addAttributeToSelect('created_at');
        $collection->addAttributeToSelect('group_id');
        $collection->addAttributeToSelect('ecc_previous_erpaccount');
        $collection->addAttributeToSelect('ecc_erpaccount_id', 'left');
        $collection->addAttributeToSelect('ecc_sales_rep_account_id', 'left');
        $collection->addAttributeToSelect('ecc_erp_account_type', 'left');
        $collection->joinTable(array('cc' => $erpaccountTable), 'entity_id=ecc_erpaccount_id', array('customer_erp_code' => 'erp_code', 'customer_company' => 'company', 'customer_short_code' => 'short_code'), null, 'left');

        $collection->addAttributeToSelect('ecc_supplier_erpaccount_id', 'left');
        $collection->joinTable(array('ss' => $erpaccountTable), 'entity_id=ecc_supplier_erpaccount_id', array('supplier_erp_code' => 'erp_code', 'supplier_company' => 'company', 'supplier_short_code' => 'short_code'), null, 'left');
        $collection->joinTable(array('sr' => $salesRepTable), 'id=ecc_sales_rep_account_id', array('sales_rep_id' => 'sales_rep_id'), null, 'left');
        $collection->addExpressionAttributeToSelect('joined_company', "IF(cc.company IS NOT NULL, cc.company, IF(ss.company IS NOT NULL, ss.company, ''))", 'ecc_erpaccount_id');
        $collection->addExpressionAttributeToSelect('joined_short_code', "IF(sr.sales_rep_id IS NOT NULL, sr.sales_rep_id, IF(cc.short_code IS NOT NULL, cc.short_code, IF(ss.short_code IS NOT NULL, ss.short_code, '')))", 'ecc_erpaccount_id');
        $collection->addExpressionAttributeToSelect('erp_account_type', "IF(cc.erp_code IS NOT NULL, 'Customer', IF(ss.erp_code IS NOT NULL, 'Supplier', IF(at_ecc_sales_rep_account_id.value IS NOT NULL, 'Sales Rep', 'Guest')))", 'ecc_erpaccount_id');
        $collection->addAttributeToFilter('ecc_erp_account_type', array('neq' => 'supplier'));
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header' => __('Name'),
            'index' => 'name',
            'column_css_class' => 'return-label',
        ));

        $this->addColumn('email', array(
            'header' => __('Email'),
            'index' => 'email'
        ));

        $this->addColumn('erp_account_type', array(
            'header' => __('Account Type'),
            'index' => 'erp_account_type',
            'filter_index' => 'ecc_erp_account_type',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\Accounttype',
            'type' => 'options',
            'options' => $this->commonAccountSelectorHelper->getAccountTypeNames(),
        ));
        //M1 > M2 Translation Begin (Rule P2-6.8)
        //if (!Mage::app()->isSingleStoreMode()) {
        if (!$this->_storeManager->isSingleStoreMode()) {
        //M1 > M2 Translation End
            $this->addColumn('website_id', array(
                'header' => __('Website'),
                'align' => 'left',
                'width' => '160px',
                'type' => 'options',
                'options' => $this->storeSystemStore->getWebsiteOptionHash(true),
                'index' => 'website_id',
            ));
        }

        $this->addColumn('customer_short_code', array(
            'header' => __('Short Code'),
            'index' => 'customer_short_code',
            'width' => '90'
        ));

        $this->getColumn('customer_short_code')->setIndex('joined_short_code');

        $this->addColumn('ecc_master_shopper', array(
            'header' => __('Master Shopper'),
            'width' => '50',
            'index' => 'ecc_master_shopper',
            'align' => 'center',
            'type' => 'options',
            'options' => array('1' => 'Yes', '0' => 'No')
        ));

        $this->addColumn('rowdata', array(
            'header' => __(''),
            'align' => 'left',
            'width' => '1',
            'name' => 'rowdata',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Rowdata',
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $row->getId();
    }

    public function getGridUrl()
    {
        $data = $this->getRequest()->getParams();
        return $this->getUrl('*/*/*', array('grid' => true, 'field_id' => $data['field_id']));
    }

}
