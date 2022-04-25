<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Right;

class Delete extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Right
{

    /**
     * @var \Epicor\Common\Model\Access\RightFactory
     */
    protected $commonAccessRightFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        \Epicor\Common\Model\Access\RightFactory $commonAccessRightFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->commonAccessRightFactory = $commonAccessRightFactory;
        $this->backendSession = $backendSession;
    }
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->commonAccessRightFactory->create();
                $model->setId($id);
                $model->delete();
                $this->backendSession->addSuccess(__('The Access Right has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->backendSession->addError($e->getMessage());
                $this->_redirect('*/*/view', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->backendSession->addError(__('Unable to find the Access Right to delete.'));
        $this->_redirect('*/*/');
    }

    }
