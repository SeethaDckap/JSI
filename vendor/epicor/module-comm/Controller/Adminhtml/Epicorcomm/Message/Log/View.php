<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Log;

class View extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Log
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->commHelper=$commHelper;
        parent::__construct($context, $commMessageLogFactory, $commMessagingHelper, $backendAuthSession);
    }


    public function execute()
    {
        $source = $this->getBackRoute();
        $sourceId = $this->getSourceParams();
        $this->_registry->register('message_log_source', $source);

        $this->_registry->register('message_log_sourceparam', $sourceId);
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->commMessageLogFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('Log entry missing'));
                $this->_redirect($source, $sourceId);
            }
        }

        $this->_registry->register('message_log_data', $model);

        $date = $this->commHelper->getLocalDate($model->getStartDatestamp());

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Comm::message');
        $resultPage->getConfig()->getTitle()
            ->prepend(__("Log entry for ". $model->getMessageType() . " at " . " $date"));

        return $resultPage;
    }


}
