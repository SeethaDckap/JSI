<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller\Order;

class Update extends \Epicor\B2b\Controller\Order
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

   
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $commMessagingHelper, $customerSession, $registry);
        $this->commMessageRequestSomFactory = $commMessageRequestSomFactory;
    }


public function execute()
    {
        $orderId = $this->getOrderId();
        $numLines = (int) $this->getRequest()->getParam('lines');

        $data = $this->getRequest()->getPost();
        if ($data && $orderId !== false) {
            $som = $this->commMessageRequestSomFactory->create();
            $som->setOrderId($orderId);
            $som->setProductArray($data['product']);
            $som->setOrderReference($data['reference']);
            if ($som->sendMessage()) {
                $this->customerSession->addSuccess($this->__($som->getStatusDescription()));
            } else {
                $this->customerSession->addError($this->__('Error in modifing order'));
                $this->customerSession->addError($this->__($som->getStatusDescription()));
            }
        }
        $encodedOrderId = $this->commMessagingHelper->encryptCustomerText($orderId);
        $this->_redirect('*/*/edit', array('order_id' => $encodedOrderId, 'lines' => $numLines));
    }
}
