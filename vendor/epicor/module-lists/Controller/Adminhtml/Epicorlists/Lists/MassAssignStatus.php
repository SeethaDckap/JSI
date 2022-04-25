<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class MassAssignStatus extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Epicor\Lists\Model\ContractFactory
     */
    protected $listsContractFactory;

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
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Lists\Helper\Data $listsHelper
    ) {
        $this->listsListModelFactory = $context->getListsListModelFactory();
        $this->listsContractFactory = $context->getListsContractFactory();
        $this->listsHelper = $listsHelper;
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Assign Status Lists
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('listid');
        $assign_status = $this->getRequest()->getParam('assign_status');
        $assignContractIds = array();
        $excludeIds = array();
        $includeIds = array();
        foreach ($ids as $id) {
            $list = $this->listsListModelFactory->create()->load($id);
            //If the list type is contract
            //Contract status is Not Active
            //But Assign status is 1
            //then don't assign the status
            if ($list->getType() == "Co") {
                $model = $this->listsContractFactory->create();
                $model->load($list->getId(), 'list_id');
                $getContractStatus = $model->getContractStatus();
            }
            if (($list->getType() == "Co") && ($getContractStatus == "I") && ($assign_status)) {
                $excludeIds[] = $id;
            } else {
                if (($list->getType() == "Co") && (!$assign_status)) {
                    $assignContractIds[] = $id;
                }
                $includeIds[] = $id;
                $list->setActive($assign_status);
                $list->save();
            }
        }

        $errorIds = rtrim(implode(',', $excludeIds), ',');
        $disableIds = rtrim(implode(',', $assignContractIds), ',');
        $changedIds = rtrim(implode(',', $includeIds), ',');

        //if (!empty($disableIds)) {
        //    Mage::helper('epicor_lists/admin')->massUpdateListContracts($disableIds);
        //}

        if (!empty($errorIds)) {
            $this->messageManager->addError(__('List Status not changed to ' . count(array_keys($excludeIds)) . ' Lists. ' . "List Id: (" . $errorIds . ")"));
        }
        if (!empty($changedIds)) {
            $this->messageManager->addSuccess(__('List Status  changed to ' . count(array_keys($includeIds)) . ' Lists. ' . "List Id: (" . $changedIds . ")"));
        }

        $this->_redirect('*/*/');
    }

}
