<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $customerResourceModelGroupCollectionFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    
    /**
     * @var \Epicor\Common\Helper\XmlFactory
     */
    protected $commonHelper;       

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerResourceModelGroupCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Epicor\Common\Helper\Data $commonHelper,
        array $data = []
    )
    {
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->customerResourceModelGroupCollectionFactory = $customerResourceModelGroupCollectionFactory;
        $this->moduleManager = $moduleManager;
        $this->commonHelper = $commonHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('entity_id');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        if(!$this->commonHelper->checkDealerLicense()) {
            $getTypes = \Epicor\Comm\Model\Customer\Erpaccount::$_All_ErpAccountsTypes_List;
            $types = array_diff($getTypes, array('Dealer', 'Distributor'));
            $collection->addFieldToFilter('account_type', array('in' => $types));
        }        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('accounts');

        //M1 > M2 Translation Begin (Rule 58)
        //$groups = $this->helper('customer')->getGroups()->toOptionArray();
        /** @var \Magento\Customer\Model\ResourceModel\Group\Collection $groupCollection */
        $groupCollection = $this->customerResourceModelGroupCollectionFactory->create();
        $groupCollection->setRealGroupsFilter()->load();
        $groups = $groupCollection->toOptionArray();
        //M1 > M2 Translation End
        $this->getMassactionBlock()->addItem('cusgroup', array(
            'label' => __('Assign Customer Group'),
            'url' => $this->getUrl('*/*/massGroupassign'),
            'confirm' => __('Are you sure?'),
            'additional' => array(
                'visibility' => array(
                    'name' => 'customerGroup',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => __('Customer Groups'),
                    'values' => $groups
                )
            )
        ));

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Delete selected ERP Accounts?')
        ));

        /* CPN EDITING REMOVED UNTIL IS COMPLETE
          $this->getMassactionBlock()->addItem('cpnediting', array(
          'label' => Mage::helper('epicor_comm')->__('Change CPN Editing'),
          'url' => $this->getUrl('<CHANGETHIS>/massCpnEditing'),
          'confirm' => Mage::helper('epicor_comm')->__('Are you sure?'),
          'additional' => array(
          'visibility' => array(
          'name' => 'cpnEditing',
          'type' => 'select',
          'label' => $this->__('CPN Editing'),
          'values' => Mage::getModel('epicor_comm/config_source_yesnonulloption')->toOptionArray()
          )
          )
          ));
         */

        if ($this->moduleManager->isEnabled('Epicor_SalesRep')) {
            $this->getMassactionBlock()->addItem('assign_sales_rep_account', array(
                'label' => __('Assign a Sales Rep Account'),
                'url' => $this->getUrl('adminhtml/epicorsalesrep_customer_salesrep/massAssignToErpAccounts'),
                'additional' => array(
                    'sales_rep_account' => array(
                        'name' => 'sales_rep_account',
                        'type' => 'Epicor\SalesRep\Block\Adminhtml\Form\Element\Salesrepaccount',
                        'renderer' => array(
                            'type' => 'sales_rep_account',
                            'class' => 'Epicor\SalesRep\Block\Adminhtml\Form\Element\Salesrepaccount'
                        ),
                        'label' => __('Sales Rep Account'),
                        'required' => true
                    //'values' => $accounts
                    )
                )
            ));
        }

        return $this;
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();


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

        $this->addColumn('account_number', array(
            'header' => __('ERP Account Number'),
            'index' => 'account_number',
            'width' => '20px',
            'filter_index' => 'account_number'
        ));

        if ($this->moduleManager->isEnabled('Epicor_Supplierconnect')) {
            $this->addColumn('account_type', array(
                'header' => __('ERP Account Type'),
                'index' => 'account_type',
                'width' => '20px',
                'filter_index' => 'account_type',
            ));
        }

        $this->addColumn('name', array(
            'header' => __('Name'),
            'index' => 'name',
            'filter_index' => 'name',
        ));

        $groups = $this->customerResourceModelGroupCollectionFactory->create()
            ->addFieldToFilter('customer_group_id', array('gt' => 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('customer_group_code', array(
            'header' => __('Customer Group'),
            'width' => '100',
            'index' => 'magento_id',
            'type' => 'options',
            'options' => $groups,
        ));

        $this->addColumn('onstop', array(
            'header' => __('On Stop'),
            'index' => 'onstop',
            'type' => 'options',
            'options' => array(
                '1' => __('Yes'),
                '0' => __('No'),
            ),
            'filter_index' => 'onstop',
        ));

        $this->addColumn('created_at', array(
            'header' => __('Created'),
            'index' => 'created_at',
            'width' => '200px',
            'filter_index' => 'created_at',
            'type' => 'datetime',
        ));

        $this->addColumn('updated_at', array(
            'header' => __('Last ERP Update'),
            'index' => 'updated_at',
            'width' => '200px',
            'type' => 'datetime',
            'filter_index' => 'updated_at',
        ));

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
