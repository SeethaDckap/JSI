<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\System\Variable;

class WysiwygPlugin extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\System\Variable
{


    /**
     * Index Action
     *
     */
//    public function indexAction()
//    {
//        $this->_title($this->__('System'))->_title($this->__('Custom Variables'));
//
//        $this->_initLayout()
//            ->_addContent($this->getLayout()->createBlock('adminhtml/system_variable'))
//            ->renderLayout();
//    }

    /**
     * New Action (forward to edit action)
     *
     */
//    public function newAction()
//    {
//        $this->_forward('edit');
//    }
//
//    /**
//     * Edit Action
//     *
//     */
//    public function editAction()
//    {
//        $variable = $this->_initVariable();
//
//        $this->_title($variable->getId() ? $variable->getCode() : $this->__('New Variable'));
//
//        $this->_initLayout()
//            ->_addContent($this->getLayout()->createBlock('adminhtml/system_variable_edit'))
//            ->_addJs($this->getLayout()->createBlock('core/template', '', array(
//                'template' => 'system/variable/js.phtml'
//            )))
//            ->renderLayout();
//    }

    /**
     * Validate Action
     *
     */
//    public function validateAction()
//    {
//        $response = new Varien_Object(array('error' => false));
//        $variable = $this->_initVariable();
//        $variable->addData($this->getRequest()->getPost('variable'));
//        $result = $variable->validate();
//        if ($result !== true && is_string($result)) {
//            $this->_getSession()->addError($result);
//            $this->_initLayoutMessages('adminhtml/session');
//            $response->setError(true);
//            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
//        }
//        $this->getResponse()->setBody($response->toJson());
//    }
//
//    /**
//     * Save Action
//     *
//     */
//    public function saveAction()
//    {
//        $variable = $this->_initVariable();
//        $data = $this->getRequest()->getPost('variable');
//        $back = $this->getRequest()->getParam('back', false);
//        if ($data) {
//            $data['variable_id'] = $variable->getId();
//            $variable->setData($data);
//            try {
//                $variable->save();
//                $this->_getSession()->addSuccess(
//                    Mage::helper('adminhtml')->__('The custom variable has been saved.')
//                );
//                if ($back) {
//                    $this->_redirect('*/*/edit', array('_current' => true, 'variable_id' => $variable->getId()));
//                } else {
//                    $this->_redirect('*/*/', array());
//                }
//                return;
//            } catch (\Exception $e) {
//                $this->_getSession()->addError($e->getMessage());
//                $this->_redirect('*/*/edit', array('_current' => true, ));
//                return;
//            }
//        }
//        $this->_redirect('*/*/', array());
//        return;
//    }
//
//    /**
//     * Delete Action
//     *
//     */
//    public function deleteAction()
//    {
//        $variable = $this->_initVariable();
//        if ($variable->getId()) {
//            try {
//                $variable->delete();
//                $this->_getSession()->addSuccess(
//                    Mage::helper('adminhtml')->__('The custom variable has been deleted.')
//                );
//            } catch (\Exception $e) {
//                $this->_getSession()->addError($e->getMessage());
//                $this->_redirect('*/*/edit', array('_current' => true, ));
//                return;
//            }
//        }
//        $this->_redirect('*/*/', array());
//        return;
//    }


    /**
     * @var \Epicor\Comm\Model\Remotelinks\VariablesFactory
     */
    protected $commRemotelinksVariablesFactory;

public function __construct(
    \Epicor\Comm\Controller\Adminhtml\Context $context,
    \Magento\Variable\Model\VariableFactory $variableVariableFactory,
    \Epicor\Comm\Model\Remotelinks\VariablesFactory $commRemotelinksVariablesFactory,
    \Magento\Backend\Model\Auth\Session $backendAuthSession)
{
    $this->commRemotelinksVariablesFactory = $commRemotelinksVariablesFactory;
    parent::__construct($context, $variableVariableFactory, $backendAuthSession);
}

    /**
     * WYSIWYG Plugin Action
     *
     */
    public function execute()
    {
        $remotelinksVariables = $this->commRemotelinksVariablesFactory->create()->toOptionArray(true);
        $variables = array($remotelinksVariables);
        $this->getResponse()->setBody(\Zend_Json::encode($variables));
    }

    }
