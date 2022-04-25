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
class Erpaccount extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
    \Magento\Backend\Block\Widget\Context $context, array $data = []
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_controller = 'adminhtml_customer_erpaccount';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Erp Accounts');
        parent::__construct(
            $context, $data
        );

        $erp = $this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (empty($erp) || !$this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/cnc_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->removeButton('add');
        } else {
            $this->updateButton('add', 'label', 'Add ERP Account');
        }
    }

    public function _prepareLayout()
    {
        if (is_object($this->getLayout()->getBlock('page.title'))) {
            $this->getLayout()->getBlock('page.title')->setPageTitle($this->getHeaderText());
        }
        return parent::_prepareLayout();
    }
}
