<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups;

class Create extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        \Epicor\Dealerconnect\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->backendSession = $context->getBackendSession();
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Group create action
     *
     * @return void
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            $dealergrp = $this->loadEntity();
            /* @var $dealergrp Epicor_Dealerconnect_Model_DealerGroups */

            $this->processDetailsSave($dealergrp, $data);
            $this->processERPAccountsSave($dealergrp, $data);

            $valid = $dealergrp->validate();
            $session = $this->backendSession;
            if ($valid === true) {
                $dealergrp->save();
                $this->messageManager->addSuccess(__('Dealer Group Saved Successfully'));
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $dealergrp->getId()));
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
