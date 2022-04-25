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
 * Description of Erpaccount
 *
 * @author David.Wylie
 */
class Customer extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
//      /  $this->scopeConfig = $context->getScopeConfig();
        $this->_controller = 'adminhtml_customer_customer';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Erp Accounts');
        parent::__construct(
            $context,
            $data
        );

    }

}
