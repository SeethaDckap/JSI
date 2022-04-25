<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Customer;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
use Magento\Store\Model\ScopeInterface;

/**
 * Description of Grid
 *
 * @author David.Wylie
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Epicor\Common\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Common\Helper\Account\Selector
     */
    protected $commonAccountSelectorHelper;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\AccounttypeFactory
     */
    protected $commAdminhtmlCustomerGridRendererAccounttypeFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $groupFactory;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    protected $countryFactory;
    /**
     * Store manager
     *
     * @var StoreManager
     */
    protected $storeManager;
    
    /**
     * @var \Epicor\Common\Helper\XmlFactory
     */
    protected $commonHelper;

    /**
        Path to config value to check Multiple customer group under CUS setting
     */
    const XML_PATH_MULTIPLE_CUSTOMER_GROUP = 'epicor_comm_field_mapping/cus_mapping/customer_use_multiple_customer_groups';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Common\Helper\Account\Selector $commonAccountSelectorHelper,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupFactory,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $countryFactory,
        \Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\AccounttypeFactory $commAdminhtmlCustomerGridRendererAccounttypeFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        array $data = []
    )
    {
        $this->commAdminhtmlCustomerGridRendererAccounttypeFactory = $commAdminhtmlCustomerGridRendererAccounttypeFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->commHelper = $commHelper;
        $this->commonAccountSelectorHelper = $commonAccountSelectorHelper;
        $this->groupFactory = $groupFactory;
        $this->countryFactory = $countryFactory;
        $this->storeManager = $context->getStoreManager();
        $this->commonHelper = $commonHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('entity_id');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareMassaction()
    {     
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('customer');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => __('Delete'),
             'url'      => $this->getUrl('adminhtml/epicorcomm_customer/massDelete'),
             'confirm'  => __('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('newsletter_subscribe', array(
             'label'    => __('Subscribe to Newsletter'),
             'url'      => $this->getUrl('adminhtml/epicorcomm_customer/massSubscribe')
        ));

        $this->getMassactionBlock()->addItem('newsletter_unsubscribe', array(
             'label'    => __('Unsubscribe from Newsletter'),
             'url'      => $this->getUrl('adminhtml/epicorcomm_customer/massUnsubscribe')
        ));

        if(!$this->_scopeConfig->isSetFlag(self::XML_PATH_MULTIPLE_CUSTOMER_GROUP,
            ScopeInterface::SCOPE_STORE)){
            $groups = $this->groupFactory->create()
                ->addFieldToFilter('customer_group_id', array('gt' => 0))
                ->load()
                ->toOptionHash(); //$this->helper('customer')->getGroups()->toOptionArray();

            array_unshift($groups, array('label'=> '', 'value'=> ''));
            $this->getMassactionBlock()->addItem('assign_group', array(
                'label'        => __('Assign a Customer Group'),
                'url'          => $this->getUrl('adminhtml/epicorcomm_customer/massAssignGroup'),
                'additional'   => array(
                    'visibility'    => array(
                        'name'     => 'group',
                        'type'     => 'select',
                        'class'    => 'required-entry',
                        'label'    => __('Group'),
                        'values'   => $groups
                    )
                )
            ));
        }

        $this->getMassactionBlock()->addItem('assignerpaccount', array(
            'label' => __('Assign an Account'),
            'url' => $this->getUrl('adminhtml/epicorcomm_customer/massAssignAccount'),
            'additional' => array(
                'ecc_erp_account_type' => array(
                    'name' => 'ecc_erp_account_type',
                    'type' => 'Epicor\Common\Block\Adminhtml\Form\Element\Erpaccounttype',
                    'renderer' => array(
                        'type' => 'account_selector',
                        'class' => 'Epicor_Comm_Block_Adminhtml_Form_Element_Erpaccount'
                    ),
                    'label' => __('Assign Account'),
                    'required' => true
                ),
            )
        ));

        $this->getMassactionBlock()->addItem('reset_password', array(
            'label' => __('Reset Password - Set Value'),
            'url' => $this->getUrl('adminhtml/epicorcomm_customer/massResetPassword'),
            'additional' => array(
                'visibility' => array(
                    'name' => 'password',
                    'type' => 'text',
                    'label' => __('New Password'),
                ),
                'status' => array(
                    'name' => 'is_email_send',
                    'type' => 'select',
                    'label' => __('Send Email'),
                    'values' => ['1'=>'Yes','0'=>'No']
                )
            )
        ));

        $this->getMassactionBlock()->addItem('reset_random_password', array(
            'label' => __('Reset Password - Randomly Generated'),
            'url' => $this->getUrl('adminhtml/epicorcomm_customer/massResetPassword'),
            'additional' => array(                
                'status' => array(
                    'name' => 'is_email_send',
                    'type' => 'select',
                    'label' => __('Send Email'),
                    'values' => ['1'=>'Yes','0'=>'No']                   
                )
            )
        ));
        
        $this->getMassactionBlock()->addItem('set_shopper', array(
            'label' => __('Set Master Shopper'),
            'url' => $this->getUrl('adminhtml/epicorcomm_customer/massSetShopper'),
        ));
        
        $this->getMassactionBlock()->addItem('revoke_shopper', array(
            'label' => __('Revoke Master Shopper'),
            'url' => $this->getUrl('adminhtml/epicorcomm_customer/massRevokeShopper'),
        ));


        return $this;
    }

    protected function _prepareCollection()
    {

        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
        /* @var $collection Mage_Customer_Model_Resource_Customer_Collection */
        $customerErpLinkTable = $collection->getTable('ecc_customer_erp_account');
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
        $collection->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left');
        $collection->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left');
        $collection->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left');
        $collection->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left');
        $collection->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
        $collection->setFlag('allow_duplicate', 1);
        $collection->joinTable(array('erp' => $customerErpLinkTable), 'customer_id=entity_id', array('erp_link' => 'erp_account_id', 'erp_contact_code' => 'contact_code'), null, 'left');
        $collection->addAttributeToSelect('erp_link');
        $collection->joinTable(array('cc' => $erpaccountTable), 'entity_id=erp_link', array('customer_erp_code' => 'erp_code', 'customer_company' => 'company', 'customer_short_code' => 'short_code', 'linked_erp_account_type'=>'account_type'), null, 'left');
        // if supplierconnect is enabled, then join the erpaccountinfo on the
        $collection->addAttributeToSelect('ecc_supplier_erpaccount_id', 'left');
        $collection->joinTable(array('ss' => $erpaccountTable), 'entity_id=ecc_supplier_erpaccount_id', array('supplier_erp_code' => 'erp_code', 'supplier_company' => 'company', 'supplier_short_code' => 'short_code'), null, 'left');
        $collection->joinTable(array('sr' => $salesRepTable), 'id=ecc_sales_rep_account_id', array('sales_rep_id' => 'sales_rep_id'), null, 'left');
        $collection->addExpressionAttributeToSelect('joined_company', "IF(cc.company IS NOT NULL, cc.company, IF(ss.company IS NOT NULL, ss.company, ''))", 'ecc_erpaccount_id');
        $collection->addExpressionAttributeToSelect('joined_short_code', "IF(sr.sales_rep_id IS NOT NULL, sr.sales_rep_id, IF(cc.short_code IS NOT NULL, cc.short_code, IF(ss.short_code IS NOT NULL, ss.short_code, '')))", 'ecc_erpaccount_id');
        $collection->addExpressionAttributeToSelect('erp_account_type', "IF(cc.erp_code IS NOT NULL, 'Customer', IF(ss.erp_code IS NOT NULL, 'Supplier', IF(at_ecc_sales_rep_account_id.value IS NOT NULL, 'Sales Rep', 'Guest')))", 'ecc_erpaccount_id');
//        } else {
//            $collection->addExpressionAttributeToSelect('erp_account_type', "IF(cc.erp_code IS NOT NULL, 'Customer', IF(ecc_sales_rep_account_id IS NOT NULL, 'Sales Rep', IF(at_ecc_sales_rep_account_id.value IS NOT NULL, 'Sales Rep', 'Guest')))", 'ecc_erpaccount_id');
//        }
        
        $helper = $this->commonHelper;
        $dealerLicense = $helper->checkDealerLicense();        
        if(!$dealerLicense) {
            $collection->getSelect()->where("cc.account_type <> 'Dealer' AND cc.account_type <> 'Distributor' OR cc.account_type is null");
        }  
     
        $this->setCollection($collection);

        return \Magento\Backend\Block\Widget\Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        
        $this->addColumn('entity_id', array(
            'header'    => __('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));
        /*$this->addColumn('firstname', array(
            'header'    => __('First Name'),
            'index'     => 'firstname'
        ));
        $this->addColumn('lastname', array(
            'header'    => __('Last Name'),
            'index'     => 'lastname'
        ));*/
        $this->addColumn('name', array(
            'header'    => __('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('email', array(
            'header'    => __('Email'),
            'width'     => '150',
            'index'     => 'email'
        ));

        $groups = $this->groupFactory->create()
            ->addFieldToFilter('customer_group_id', array('gt' => 0))
            ->load()
            ->toOptionHash(); ; 

        $this->addColumn('group', array(
            'header'    =>  __('Group'),
            'width'     =>  '100',
            'index'     =>  'group_id',
            'type'      =>  'options',
            'options'   =>  $groups,
        ));

        $this->addColumn('Telephone', array(
            'header'    => __('Telephone'),
            'width'     => '100',
            'index'     => 'billing_telephone',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\Addresses',
        ));

        $this->addColumn('billing_postcode', array(
            'header'    => __('ZIP'),
            'width'     => '90',
            'index'     => 'billing_postcode',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\Addresses',
        ));

        $this->addColumn('billing_country_id', array(
            'header'    => __('Country'),
            'width'     => '100',
            'type'      => 'country',
            'index'     => 'billing_country_id',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\Addresses',
        ));

        $this->addColumn('billing_region', array(
            'header'    => __('State/Province'),
            'width'     => '100',
            'index'     => 'billing_region',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\Addresses',
        ));

        $this->addColumn('customer_since', array(
            'header'    => __('Customer Since'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));

        if (!$this->storeManager->isSingleStoreMode()) {
            
            $websites = $this->countryFactory->create()->load()->toOptionHash();
            $this->addColumn('website_id', array(
                'header'    => __('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => $this->countryFactory->create()->load()->toOptionHash(),
                'index'     => 'website_id',
            ));
        }

        $this->addColumnAfter('ecc_master_shopper', array(
            'header' => __('Master Shopper'),
            // 'type'      => 'text',
            'align' => 'center',
            'index' => 'ecc_master_shopper',
            'type' => 'options',
            'options' => array('1' => 'Yes', '0' => 'No')
            ), 'group');

        $this->addColumnAfter('customer_company', array(
            'header' => __('Company'),
            'index' => 'customer_company',
            'width' => '90',
            ), 'group');

        $this->addColumnAfter('customer_short_code', array(
            'header' => __('Short Code'),
            'index' => 'customer_short_code',
            'width' => '90',
            ), 'customer_company');

        if ($this->commHelper->isModuleEnabled('Epicor_Supplierconnect')) {
            $this->getColumn('customer_company')->setIndex('joined_company');
            $this->getColumn('customer_short_code')->setIndex('joined_short_code');
        }

        $this->addColumnAfter('ecc_previous_erpaccount', array(
            'header' => __('Previous'),
            'index' => 'ecc_previous_erpaccount',
            ), 'customer_short_code');

        $this->addColumnAfter('erp_account_type', array(
            'header' => __('Account Type'),
            'index' => 'erp_account_type',
            'filter_index' => 'ecc_erp_account_type',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\Accounttype',
            'type' => 'options',
           // 'options' => $this->commonAccountSelectorHelper->getAccountTypeNames(),
            'options' =>$this->getAccountSelector(), 
            'filter_condition_callback' => array($this, 'filterbyErpAccountCallback')
            ), 'customer_short_code');

        $this->removeColumn('entity_id');
      //  $this->sortColumnsByOrder();
       
        $this->addColumn('action',
            array(
                'header'    =>  __('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => __('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        $this->addExportType('adminhtml/epicorcomm_customer/exportCsv/', __('CSV'));
        $this->addExportType('adminhtml/epicorcomm_customer/exportXml/', __('Excel XML'));
        parent::_prepareColumns();
        return $this;
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    
    public function getAccountSelector(){
        $list = $this->commonAccountSelectorHelper->getAccountTypeNames();
        $helper = $this->commonHelper;
        $dealerLicense = $helper->checkDealerLicense();
        if($dealerLicense) {
            $list['Dealer'] = 'Dealer';
            $list['Distributor'] = 'Distributor';
        }
        return $list;
    }
    
    public function filterbyErpAccountCallback($collection, $column){
        if (!$value = $column->getFilter()->getValue()) 
        {
            return $this;
        }
        if($value=='Dealer' || $value=='Distributor'){
            $this->getCollection()->getSelect()->where("cc.account_type like ?", "%$value%");
        }else if($value =='customer'){
            $this->getCollection()->getSelect()->where("cc.account_type NOT IN (?)", array('Dealer','Distributor'));
        }else{
            $this->getCollection()->getSelect()->where("at_ecc_erp_account_type.value like ?", "%$value%");
        }
        return $this;
    }
}
