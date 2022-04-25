<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Create extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->backendSession = $context->getBackendSession();
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * List create action
     *
     * @return void
     */
    public function execute()
    {
        $helper = $this->listsHelper;
        /* @var $helper Epicor_Lists_Helper_Data */

        if ($data = $this->getRequest()->getPost()) {
            $list = $this->loadEntity();
            /* @var $list Epicor_Lists_Model_ListModel */

            $this->processDetailsSave($list, $data);

            $valid = $list->validate();
            $session = $this->backendSession;

            if ($valid === true) {
                $importProductErrors = $this->importProducts($list);
                $importAddressesErrors = $this->importAddresses($list);
                $list->save();
                $this->processContractFieldSave($list, $data);
                $this->messageManager->addSuccess(__('List Saved Successfully'));
                if ($importProductErrors || $this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $list->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } else {
                $this->messageManager->addError(__('The Following Error(s) occurred on Save:'));
                foreach ($valid as $error) {
                    $this->messageManager->addError($error);
                }
                $session->setFormData($data);
                $this->_redirect('*/*/new');
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

    }
