<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Dashboard;


class Stats extends \Magento\Framework\View\Element\Template
{

    /**
     *  @var \Magento\Framework\DataObject 
     */
    protected $_stats = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\SalesRep\Helper\Data
     */
    protected $salesRepHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->salesRepHelper = $salesRepHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('Epicor_SalesRep::epicor/salesrep/account/dashboard/stats.phtml');
        $this->setTitle(__('Stats'));
        $this->setColumnCount(2);

        $salesRepAccount = $this->registry->registry('sales_rep_account');
        /* @var $salesRepAccount \Epicor\SalesRep\Model\Account */

        $this->_stats[1] = array(
            'Your ERP Accounts:' => array('value' => count($salesRepAccount->getErpAccounts(true)), 'link' => $this->getUrl('*/account_manage/erpaccounts')),
            'Your Price Lists:' => array('value' => 0, 'link' => '')
        );

        if (count($salesRepAccount->getChildAccounts(true)) > 0) {
            $this->_stats[2] = array(
               'Total ERP Accounts Under You:' => array('value' => count($salesRepAccount->getMasqueradeAccounts(true)), 'link' => $this->getUrl('*/account_manage/erpaccounts')),
               'Total Sales Rep Price Lists:' => array('value' => 0, 'link' => ''),
                'Sales Reps Reporting to You:' => array('value' => count($salesRepAccount->getChildAccounts(true)), 'link' => $this->getUrl('*/account_manage/hierarchy'))
            );
        }
    }

    /**
     * 
     * @return \Epicor\Supplierconnect\Helper\Data
     */
    public function getHelper($type = null)
    {
        //M1 > M2 Translation Begin (Rule p2-7)
        //return Mage::helper('epicor_salesrep');
        return $this->salesRepHelper;
        //M1 > M2 Translation End

    }

    //M1 > M2 Translation Begin (Rule 56)
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getStatus()
    {
        return $this->_stats;
    }
    //M1 > M2 Translation End

}
