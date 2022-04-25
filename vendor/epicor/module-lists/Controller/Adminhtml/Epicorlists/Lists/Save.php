<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Save extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;
    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListProductPositionFactory
     */
    private $listProductPositionFactory;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendSession,
        \Epicor\Lists\Model\ResourceModel\ListProductPositionFactory $listProductPositionFactory = null
    ) {
        $this->backendSession = $backendSession;
        parent::__construct($context, $backendSession);
        $this->listProductPositionFactory = $listProductPositionFactory;
    }
    /**
     * List save action
     *
     * @return void
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPost()) {
            $list = $this->loadEntity();
            /* @var $list Epicor_Lists_Model_ListModel */

            $this->processDetailsSave($list, $data);
            $this->processContractFieldSave($list, $data);

            $typeInstance = $list->getTypeInstance();
            if ($typeInstance->isSectionEditable('labels')) {
                $this->processLabelsSave($list, $data);
            }
            if ($typeInstance->isSectionEditable('erpaccounts')) {
                $this->processERPAccountsSave($list, $data);
            }
            if ($typeInstance->isSectionEditable('websites')) {
                $this->processWebsitesSave($list, $data);
            }
            if ($typeInstance->isSectionEditable('stores')) {
                $this->processStoresSave($list, $data);
            }
            if ($typeInstance->isSectionEditable('customers')) {
                $this->processCustomersSave($list, $data);
            }
            if ($typeInstance->isSectionEditable('products')) {
                $this->processProductsSave($list, $data);
            }
            if ($typeInstance->isSectionEditable('pricing')) {
                $this->processProductsPricingSave($list, $data);
            }
            if ($typeInstance->isSectionEditable('addresses')) {
                $this->processAddressesSave($list, $data);
            }
            $this->processConditionsSave($list, $data);

            $list->orphanCheck();
            $valid = $list->validate();
            $session = $this->backendSession;

            if ($valid === true) {
                $importProductErrors =$this->importProducts($list);
                $list->save();
                $productLinkData = $this->getRequest()->getParam('links');
                $assignedProducts = $data['assign-products'] ?? '';
                if($this->listProductPositionFactory){
                    $listPosition = $this->listProductPositionFactory->create();
                    /** @var $listPosition \Epicor\Lists\Model\ResourceModel\ListProductPosition */
                    $listPosition->saveProductPositionJsonData($list, $assignedProducts, $productLinkData);
                }

                $session = $this->backendSession;
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
                $this->_redirect('*/*/edit', array('id' => $list->getId()));
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

}
