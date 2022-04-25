<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Returns;

class Logsgrid extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Returns {

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModelFactory
     */
    protected $commCustomerReturnModelFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->commCustomerReturnModelFactory = $commCustomerReturnModelFactory;
        $this->registry = $context->getRegistry();
        parent::__construct($context, $backendAuthSession);
    }

    public function execute() {

        $return = $this->commCustomerReturnModelFactory->create()->load($this->getRequest()->get('id'));
        /* @var $quote Epicor_Comm_Model_Customer_ReturnModel */
        $this->registry->register('return', $return);
        $block = $this->_view->getLayout()->createBlock('Epicor\Comm\Block\Adminhtml\Sales\Returns\View\Tab\Log')
                ->setUseAjax(true);
        $this->getResponse()->setBody($block->toHtml());
    }

}
