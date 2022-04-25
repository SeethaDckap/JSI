<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Addgrid extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context, $backendAuthSession);
        $this->listsListModelFactory = $context->getListsListModelFactory();
    }
    public function execute()
    {
        //$list = $this->loadEntity();
        $data = $this->getRequest()->getPost();
        $list = $this->listsListModelFactory->create()->load($data['listId']);
        if ($list) {
            if ($data['linkTypeValue']) {
                $selectedRestricionType = $data['linkTypeValue'];
                $this->backendAuthSession->setRestrictionTypeValue($selectedRestricionType);
            }
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('restrictions_grid');
        $this->_view->renderLayout();
    }

}
