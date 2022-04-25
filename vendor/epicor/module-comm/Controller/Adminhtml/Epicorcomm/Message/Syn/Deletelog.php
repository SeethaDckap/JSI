<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Syn;

class Deletelog extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Syn
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Epicor\Comm\Model\Syn\LogFactory
     */
    protected $commSynLogFactory;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Model\Syn\LogFactory $commSynLogFactory,
        \Epicor\Comm\Helper\Data $commHelper
    ) {
        $this->backendSession = $context->getSession();
        $this->commSynLogFactory = $commSynLogFactory;
        $this->commHelper = $commHelper;
        parent::__construct($context, $backendAuthSession, $commMessageLogFactory, $commMessagingHelper);
    }
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('logid');
        $count = count($ids);
        $session = $this->messageManager;

        foreach ($ids as $id) {
            $model = $this->commSynLogFactory->create();
            /* @var $model Epicor_Comm_Model_Syn_Log */
            $model->load($id);
            if ($model->getId()) {
                if (!$model->delete()) {
                    $session->addError(__('Could not delete SYN Log ' . $id));
                    $count--;
                }
            }
        }

        $helper = $this->commHelper;
        //M1 > M2 Translation Begin (Rule 55)
        //$session->addSuccess($this->__('%s SYN log entries deleted', $count));
        $session->addSuccess(__('%1 SYN log entries deleted', $count));
        //M1 > M2 Translation End

        $this->_redirect('*/*/log');
    }

    }
