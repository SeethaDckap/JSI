<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\System;


abstract class Variable extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    /**
     * @var \Magento\Variable\Model\VariableFactory
     */
    protected $variableVariableFactory;

public function __construct(
    \Epicor\Comm\Controller\Adminhtml\Context $context,
    \Magento\Variable\Model\VariableFactory $variableVariableFactory,
    \Magento\Backend\Model\Auth\Session $backendAuthSession)
{
    $this->variableVariableFactory = $variableVariableFactory;
    parent::__construct($context, $backendAuthSession);
}

    /**
     * Initialize Layout and set breadcrumbs
     *
     * @return Mage_Adminhtml_System_VariableController
     */
    protected function _initLayout()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/variable')
            ->_addBreadcrumb(__('Custom Variables'), __('Custom Variables'));
        return $this;
    }

    /**
     * Initialize Variable object
     *
     * @return \Magento\Variable\Model\Variable
     */
    protected function _initVariable()
    {
        $this->_title(__('System'))->_title(__('Custom Variables'));

        $variableId = $this->getRequest()->getParam('variable_id', null);
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        /* @var $emailVariable Mage_Core_Model_Variable */
        $variable = $this->variableVariableFactory->create();
        if ($variableId) {
            $variable->setStoreId($storeId)
                ->load($variableId);
        }
        $this->_registry->register('current_variable', $variable);
        return $variable;
    }
/**
     * Check current user permission
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('system/variable');
    }

}

