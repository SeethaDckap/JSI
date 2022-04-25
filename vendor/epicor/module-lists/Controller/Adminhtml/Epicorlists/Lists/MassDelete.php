<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class MassDelete extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
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
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Deletes array of given List
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('listid');
        foreach ($ids as $id) {
            $this->delete($id, true);
        }
        $this->messageManager->addSuccess(__(count($ids) . ' Lists deleted'));
        $this->_redirect('*/*/');
    }

}
