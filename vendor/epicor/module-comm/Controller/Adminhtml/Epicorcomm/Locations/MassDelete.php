<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locations;

class MassDelete extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locations
{

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $commLocationFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\LocationFactory $commLocationFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->commLocationFactory = $commLocationFactory;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('locationid');
        $model = $this->commLocationFactory->create();
        foreach ($ids as $id) {
            $model->setId($id);
            $model->delete();
        }
        $this->messageManager->addSuccessMessage(__(count($ids) .' Message log entries deleted'));
        $this->_redirect('*/*/');
    }

    }
