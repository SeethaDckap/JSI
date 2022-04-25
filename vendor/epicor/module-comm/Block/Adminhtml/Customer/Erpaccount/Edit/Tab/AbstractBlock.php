<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Abstract
 *
 * @author David.Wylie
 */
class AbstractBlock extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_erp_customer;
    protected $_title = 'Title';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * 
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getErpCustomer()
    {
        if (!$this->_erp_customer) {
            $this->_erp_customer = $this->registry->registry('customer_erp_account');
        }
        return $this->_erp_customer;
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return $this->_title;
    }

    public function getTabTitle()
    {
        return $this->_title;
    }

    public function isHidden()
    {
        return false;
    }

    //M1 > M2 Translation Begin (Rule 56)
    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }
    //M1 > M2 Translation End

}
