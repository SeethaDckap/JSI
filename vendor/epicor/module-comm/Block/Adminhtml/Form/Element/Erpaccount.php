<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Form\Element;


class Erpaccount extends \Epicor\Common\Block\Adminhtml\Form\Element\Erpaccounttype
{

    /*public function __construct(
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Helper\Account\Selector $commonAccountSelectorHelper,
        \Epicor\Common\Model\AccountTypeModelReader $accountTypeModelReader,
        array $attributes = [])
    {
        $this->_restrictToType = 'customer';
        parent::__construct($registry, $commonHelper, $scopeConfig, $commonAccountSelectorHelper, $accountTypeModelReader, $attributes);
    }*/

    protected function _construct()
    {
        $this->_restrictToType = 'customer';
        parent::_construct();
    }

}
