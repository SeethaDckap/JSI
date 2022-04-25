<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Edit;


class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    private $_salesrep;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $jsonEncoder,
            $authSession,
            $data
        );
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form'); // this should be same as the form id define above
        $this->setTitle('Sales Rep Account');
    }

    /**
     *
     * @return \Epicor\SalesRep\Model\Account
     */
    public function getSalesRepAccount()
    {
        if (!$this->_salesrep) {
            $this->_salesrep = $this->registry->registry('salesrep_account');
        }

        return $this->_salesrep;
    }

    protected function _beforeToHtml()
    {
        $salesRep = $this->getSalesRepAccount();


        $this->addTab('form_erp_accounts', array(
            'label' => 'ERP Accounts',
            'title' => 'ERP Accounts',
            'url' => $this->getUrl('*/*/erpaccounts', array('id' => $salesRep->getId(), '_current' => true)),
            'class' => 'ajax'
        ));

        $this->addTab('salesreps', array(
            'label' => 'Sales Reps',
            'title' => 'Sales Reps',
            'url' => $this->getUrl('*/*/salesreps', array('id' => $salesRep->getId(), '_current' => true)),
            'class' => 'ajax'
        ));

        $this->addTab('pricing_rules', array(
            'label' => 'Pricing Rules',
            'title' => 'Pricing Rules',
            'url' => $this->getUrl('*/*/pricingrules', array('id' => $salesRep->getId(), '_current' => true)),
            'class' => 'ajax'
        ));

        $this->addTab('hierarchy', array(
            'label' => 'Hierarchy',
            'title' => 'Hierarchy',
            'url' => $this->getUrl('*/*/hierarchy', array('id' => $salesRep->getId(), '_current' => true)),
            'class' => 'ajax'
        ));

        return parent::_beforeToHtml();
    }

}
