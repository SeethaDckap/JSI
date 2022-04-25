<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Group;

class Delete extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Group
{

    /**
     * @var \Epicor\Common\Model\Access\GroupFactory
     */
    protected $commonAccessGroupFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        \Epicor\Common\Model\Access\GroupFactory $commonAccessGroupFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->commonAccessGroupFactory = $commonAccessGroupFactory;
        $this->backendSession = $backendSession;
    }
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->commonAccessGroupFactory->create();
                /* @var $model Epicor_Common_Model_Access_Group */

                $model->setId($id);
                $model->delete();
                $this->backendSession->addSuccess(__('The Access Group has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->backendSession->addError($e->getMessage());
                $this->_redirect('*/*/view', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->backendSession->addError(__('Unable to find the Access Group to delete.'));
        $this->_redirect('*/*/');
    }

    }
