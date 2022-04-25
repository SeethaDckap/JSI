<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Grid
 *
 * @author David.Wylie
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
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

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Common\Helper\Account\Selector $commonAccountSelectorHelper,
        \Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer\AccounttypeFactory $commAdminhtmlCustomerGridRendererAccounttypeFactory
    )
    {
        $this->commAdminhtmlCustomerGridRendererAccounttypeFactory = $commAdminhtmlCustomerGridRendererAccounttypeFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->commHelper = $commHelper;
        $this->commonAccountSelectorHelper = $commonAccountSelectorHelper;
        parent::__construct();
        $this->setDefaultSort('name');
    }

    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();


        $this->getMassactionBlock()->addItem('assign_account_type', array(
            'label' => __('Assign an Account'),
            'url' => $this->getUrl('adminhtml/epicorcommon_customer/massAssignAccount'),
            'additional' => array(
                'ecc_erp_account_type' => array(
                    'name' => 'ecc_erp_account_type',
                    'type' => 'account_selector',
                    'renderer' => array(
                        'type' => 'account_selector',
                        'class' => 'Epicor_Common_Block_Adminhtml_Form_Element_Erpaccounttype'
                    ),
                    'label' => __('Assign Account'),
                )
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
                )
            )
        ));

        $this->getMassactionBlock()->addItem('reset_random_password', array(
            'label' => __('Reset Password - Randomly Generated'),
            'url' => $this->getUrl('adminhtml/epicorcomm_customer/massResetPassword'),
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
        $collection->joinTable(array('cc' => $erpaccountTable), 'entity_id=ecc_erpaccount_id', array('customer_erp_code' => 'erp_code', 'customer_company' => 'company', 'customer_short_code' => 'short_code'), null, 'left');

//        if (Mage::helper('epicor_comm')->isModuleEnabled('Epicor_Supplierconnect')) {
        // if supplierconnect is enabled, then join the erpaccountinfo on the 
        $collection->addAttributeToSelect('ecc_supplier_erpaccount_id', 'left');
        $collection->joinTable(array('ss' => $erpaccountTable), 'entity_id=ecc_supplier_erpaccount_id', array('supplier_erp_code' => 'erp_code', 'supplier_company' => 'company', 'supplier_short_code' => 'short_code'), null, 'left');
        $collection->joinTable(array('sr' => $salesRepTable), 'id=ecc_sales_rep_account_id', array('sales_rep_id' => 'sales_rep_id'), null, 'left');
        $collection->addExpressionAttributeToSelect('joined_company', "IF(cc.company IS NOT NULL, cc.company, IF(ss.company IS NOT NULL, ss.company, ''))", 'ecc_erpaccount_id');
        $collection->addExpressionAttributeToSelect('joined_short_code', "IF(sr.sales_rep_id IS NOT NULL, sr.sales_rep_id, IF(cc.short_code IS NOT NULL, cc.short_code, IF(ss.short_code IS NOT NULL, ss.short_code, '')))", 'ecc_erpaccount_id');
        $collection->addExpressionAttributeToSelect('erp_account_type', "IF(cc.erp_code IS NOT NULL, 'Customer', IF(ss.erp_code IS NOT NULL, 'Supplier', IF(at_sales_rep_account_id.value IS NOT NULL, 'Sales Rep', 'Guest')))", 'ecc_erpaccount_id');
//        } else {
//            $collection->addExpressionAttributeToSelect('erp_account_type', "IF(cc.erp_code IS NOT NULL, 'Customer', IF(sales_rep_account_id IS NOT NULL, 'Sales Rep', IF(at_sales_rep_account_id.value IS NOT NULL, 'Sales Rep', 'Guest')))", 'ecc_erpaccount_id');
//        }
        $this->setCollection($collection);

        return \Magento\Backend\Block\Widget\Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

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
            'renderer' => $this->commAdminhtmlCustomerGridRendererAccounttypeFactory->create(),
            'type' => 'options',
            'options' => $this->commonAccountSelectorHelper->getAccountTypeNames(),
            ), 'customer_short_code');

        $this->removeColumn('entity_id');
        $this->sortColumnsByOrder();
        return $this;
    }

}
