<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locationgroups;

class MassDelete extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    /**
     * @var \Epicor\Comm\Model\Location\GroupsFactory
     */
    protected $commLocationgroupFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Location\GroupsFactory $commLocationgroupFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->commLocationgroupFactory = $commLocationgroupFactory;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('groupid');
        $model = $this->commLocationgroupFactory->create();
        foreach ($ids as $id) {
            $model->setId($id);
            $model->delete();
        }
        $this->messageManager->addSuccessMessage(__(count($ids) .' Message log entries deleted'));
        $this->_redirect('*/*/');
    }

    }
