<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups;

class Save extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        \Epicor\Dealerconnect\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendSession
    ) {
        $this->backendSession = $backendSession;
        parent::__construct($context, $backendSession);
    }
    /**
     * Group save action
     *
     * @return void
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            $dealerGrp = $this->loadEntity();
            /* @var $dealerGrp Epicor_Dealerconnect_Model_DealerGroups */

            $this->processDetailsSave($dealerGrp, $data);
            $this->processERPAccountsSave($dealerGrp, $data);

            $valid = $dealerGrp->validate();
            $session = $this->backendSession;
            if ($valid === true) {
                $dealerGrp->save();
                $this->messageManager->addSuccess(__('Dealer Group Saved Successfully'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $dealerGrp->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } else {
                $this->messageManager->addError(__('The Following Error(s) occurred on Save:'));
                foreach ($valid as $error) {
                    $this->messageManager->addError($error);
                }
                $session->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $dealerGrp->getId()));
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

}
