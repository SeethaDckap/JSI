<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var mixed|null
     */
    private $customerAccount;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->moduleManager = $moduleManager;
        parent::__construct(
            $context,
            $jsonEncoder,
            $authSession,
            $data
        );
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form'); // this should be same as the form id define above
        $this->setTitle('Erp Account');
    }

    protected function _beforeToHtml()
    {
        $customer = $this->registry->registry('customer_erp_account');
//      $leftBlock=$this->getLayout()->createBlock('core/text')
////                ->setText('<h1>Left Block</h1>');
        // add grid to erp info details tab
        $detailsBlock = $this->getLayout()->createBlock('Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Erpinfo');
        $detailsBlock->
            setChild('currencygrid', $this->getLayout()->createBlock('Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Erpcurrencygrid'));

        $this->addTab('form_details', array(
            'label' => 'Details',
            'title' => 'Details',
            'content' => $detailsBlock->toHtml(),
        ));

        $addressBlock = $this->getLayout()->createBlock('\Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Address');

        $erpAccount = $this->registry->registry('customer_erp_account');

        if ($erpAccount->isTypeCustomer()) {
            $this->addTab('form_address', array(
                'label' => 'Addresses',
                'title' => 'Addresses',
                'content' => $addressBlock->toHtml(),
            ));
        }

        $this->addTab('form_customers', array(
            'label' => 'Customers',
            'title' => 'Customers',
            'url' => $this->getUrl('*/*/customers', array('id' => $customer->getId(), '_current' => true)),
            'class' => 'ajax',
        ));

        $this->addTab('form_stores', array(
            'label' => 'Stores',
            'title' => 'Stores',
            'url' => $this->getUrl('*/*/stores', array('id' => $customer->getId(), '_current' => true)),
            'class' => 'ajax',
        ));

        if ($this->moduleManager->isEnabled('Epicor_SalesRep') && !$erpAccount->isTypeSupplier()) {
            $this->addTab('form_salesreps', array(
                'label' => 'Sales Reps',
                'title' => 'Sales Reps',
                'url' => $this->getUrl('*/*/salesreps', array('id' => $customer->getId(), '_current' => true)),
                'class' => 'ajax',
            ));
        }

        if (!$erpAccount->isTypeSupplier()) {
            $this->addTab('form_locations', array(
                'label' => 'Locations',
                'title' => 'Locations',
                'url' => $this->getUrl('*/*/locations', array('id' => $customer->getId(), '_current' => true)),
                'class' => 'ajax',
            ));
        }

        if ($erpAccount->isTypeCustomer()) {
//            $skuBlock = $this->getLayout()->createBlock('epicor_comm/adminhtml_customer_erpaccount_edit_tab_sku');
            $this->addTab('form_sku', array(
                'label' => 'Customer Sku',
                'title' => 'Customer Sku',
                'url' => $this->getUrl('*/*/skutab', array('id' => $customer->getId(), '_current' => true)),
//                'content' => $skuBlock->toHtml(),
                'class' => 'ajax'
            ));

            $this->addTab('valid_payment_method', array(
                'label' => 'Valid Payment Methods',
                'title' => 'Valid Payment Methods',
                'url' => $this->getUrl('*/*/payments', array('id' => $customer->getId(), '_current' => true)),
                'class' => 'ajax'
            ));

            $this->addTab('valid_delivery_method', array(
                'label' => 'Valid Delivery Method',
                'title' => 'Valid Delivery Method',
                'url' => $this->getUrl('*/*/delivery', array('id' => $customer->getId(), '_current' => true)),
                'class' => 'ajax'
            ));
              $this->addTab('valid_shipstatus_method', array(
                'label' => 'Valid Ship Status',
                'title' => 'Valid Ship Status',
                'url' => $this->getUrl('*/*/shipstatus', array('id' => $customer->getId(), '_current' => true)),
                'class' => 'ajax'
            ));
        }

        $hierarchyBlock = $this->getLayout()->createBlock('Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Hierarchy');

        $this->addTab('heirarchy', array(
            'label' => 'Hierarchy',
            'title' => 'Hierarchy',
            'content' => $hierarchyBlock->toHtml(),
        ));

        //$logBlock = $this->getLayout()->createBlock('epicor_comm/adminhtml_customer_erpaccount_edit_tab_log');
        //Hide the Tab Master Shoppers and Lists (If the Account Type is Supplier)
        if (!$erpAccount->isTypeSupplier()) {
            $this->addTab('ecc_master_shopper', array(
                'label' => 'Master Shoppers',
                'title' => 'Master Shoppers',
                'url' => $this->getUrl('*/*/mastershopper', array('id' => $customer->getId(), '_current' => true)),
                //'content' => $logBlock->toHtml(),
                'class' => 'ajax'
            ));
            $this->addTab('lists', array(
                'label' => 'Lists',
                'title' => 'Lists',
                'url' => $this->getUrl('*/*/lists', array('id' => $customer->getId(), '_current' => true)),
                'class' => 'ajax'
            ));
        }

        $this->addTab('form_log', array(
            'label' => 'Message Log',
            'title' => 'Message Log',
            'url' => $this->getUrl('*/*/logsgrid', array('id' => $customer->getId(), '_current' => true)),
            //'content' => $logBlock->toHtml(),
            'class' => 'ajax'
        ));
        $this->customerAccount = $customer;
        $this->getAdditionalTabs();

        return parent::_beforeToHtml();
    }

    /**
     * @return \Epicor\Dealerconnect\Model\Customer\Erpaccount
     */
    public function getCustomerAccount()
    {
        return $this->customerAccount;
    }

    /**
     * @return string
     */
    public function getAdditionalTabs()
    {
        return '';
    }

}
