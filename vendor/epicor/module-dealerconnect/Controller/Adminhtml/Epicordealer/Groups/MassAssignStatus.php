<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups;

class MassAssignStatus extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups
{

    /**
     * @var \Epicor\Dealerconnect\Model\DealergroupsFactory
     */
    protected $dealerGroupModelFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        \Epicor\Dealerconnect\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->dealerGroupModelFactory = $context->getDealerModelFactory();
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Assign Status
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('groupid');
        $assign_status = $this->getRequest()->getParam('assign_status');
        $assignContractIds = array();
        $excludeIds = array();
        $includeIds = array();
        foreach ($ids as $id) {
            $dealerGrp = $this->dealerGroupModelFactory->create()->load($id);
            if (($dealerGrp->getType() == "Co") && (!$assign_status)) {
                $assignContractIds[] = $id;
            }
            $includeIds[] = $id;
            $dealerGrp->setActive($assign_status);
            $dealerGrp->save();
        }

        $errorIds = rtrim(implode(',', $excludeIds), ',');
        $disableIds = rtrim(implode(',', $assignContractIds), ',');
        $changedIds = rtrim(implode(',', $includeIds), ',');

        if (!empty($errorIds)) {
            $this->messageManager->addError(__('Dealer Group Status not changed to ' . count(array_keys($excludeIds)) . ' Groups. ' . "Group Id: (" . $errorIds . ")"));
        }
        if (!empty($changedIds)) {
            $this->messageManager->addSuccess(__('Dealer Group Status  changed to ' . count(array_keys($includeIds)) . ' Groups. ' . "Group Id: (" . $changedIds . ")"));
        }

        $this->_redirect('*/*/');
    }

}
