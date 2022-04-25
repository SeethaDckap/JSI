<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Attribute;


/**
 * 
 * ERP Account grid for erp account selector input
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory
     */
    protected $configcollectionFactory;
    
    protected $commonHelper;           

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configcollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        array $data = []
    )
    {
        $this->configcollectionFactory = $configcollectionFactory;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->commonHelper = $commonHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('erp_account_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setRowClickCallback('accountSelector.selectAccount.bind(accountSelector)');
        $this->setRowInitCallback('accountSelector.updateWrapper.bind(accountSelector)');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();

        $data = $this->getRequest()->getParams();

        if (isset($data['field_id']) && $data['field_id'] != 'erp_account_id') {
            $this->addAccountTypeFilter($collection, $data['field_id']);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function addAccountTypeFilter(&$collection, $fieldName)
    {
        $types = \Epicor\Comm\Model\Customer\Erpaccount::$_All_ErpAccountsTypes_List;
        if(!$this->commonHelper->checkDealerLicense()) {
            $types = array_diff($types, array('Dealer', 'Distributor'));
        }
        if ($this->getRequest()->getParam('punchout_filter')) {
            $types = array_diff($types, ['Supplier', 'B2C']);
        }

        $collection->addFieldToFilter('account_type', array('in' => $types));

        if (strpos($fieldName, 'default') === false && strpos($fieldName, 'scheduledmsqcustomer') === false && strpos($fieldName, 'msq_after_stk_customer') === false) {
            //M1 > M2 Translation Begin (Rule p2-1)
            //$allStoresDefaultErpAccounts = Mage::getModel('core/config_data')->addFieldToFilter('path', array('eq' => 'customer/create_account/default_erpaccount')); // get default erp accounts for all stores
            //$allStoresDefaultErpAccounts = $this->_scopeConfig->getValue('customer/create_account/default_erpaccount'); // get default erp accounts for all stores
            $allStoresDefaultErpAccounts = $this->configcollectionFactory->create()->addFieldToFilter('path', array('eq' => 'customer/create_account/default_erpaccount'));

            //M1 > M2 Translation End
            $allDefaultErpValues = array();
            foreach ($allStoresDefaultErpAccounts as $eachStoreErpAccounts) {
                $allDefaultErpValues[] = $eachStoreErpAccounts->getValue();
            }

            // only include if not default erp account
            if (!$this->getRequest()->getParam('punchout_filter')) {
                $collection->addFieldToFilter('entity_id', ['nin' => $allDefaultErpValues]);
            }
        }
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

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

        $this->addColumn('account_type', array(
            'header' => __('ERP Account Type'),
            'index' => 'account_type',
            'width' => '20px',
            'filter_index' => 'account_type',
        ));

        $this->addColumn('company', array(
            'header' => __('Company'),
            'index' => 'company',
            'width' => '20px',
            'filter_index' => 'company'
        ));
        $this->addColumn('short_code', array(
            'header' => __('Short Code'),
            'index' => 'short_code',
            'width' => '20px',
            'filter_index' => 'short_code'
        ));

        $this->addColumn('erp_code', array(
                'header'       => __('ERP Code'),
                'index'        => 'erp_code',
                'width'        => '20px',
                'filter_index' => 'erp_code'
        ));

        $this->addColumn('account_number', array(
            'header' => __('Account Number'),
            'index' => 'account_number',
            'width' => '20px',
            'filter_index' => 'account_number'
        ));

        $this->addColumn('name', array(
            'header' => __('Name'),
            'index' => 'name',
            'filter_index' => 'name',
            'column_css_class' => 'return-label',
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
        $punchoutFilter = $this->getRequest()->getParam('punchout_filter');
        return $this->getUrl(
            '*/*/*',
            [
                'grid' => true,
                'field_id' => $data['field_id'],
                'punchout_filter'  => $punchoutFilter
            ]
        );
    }

}
