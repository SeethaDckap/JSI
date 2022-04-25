<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups;

class MassDelete extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups
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
     * Deletes array of given List
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('groupid');
        foreach ($ids as $id) {
            $this->delete($id, true);
        }
        $this->messageManager->addSuccess(__(count($ids) . ' Dealer group deleted'));
        $this->_redirect('*/*/');
    }

}
