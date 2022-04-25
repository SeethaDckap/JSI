<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller;


/**
 * Order controller
 *
 * @category   Epicor
 * @package    Epicor_B2b
 * @author     Epicor Websales Team
 */
abstract class Order extends \Magento\Framework\App\Action\Action
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
        $this->commMessagingHelper = $commMessagingHelper;
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        
        parent::__construct(
            $context
        );
    }

/**
     * Init layout, messages and set active block for customer
     *
     * @return null
     */
    protected function _viewPage($handle = null)
    {
        if (!$this->_loadValidOrder()) {
            return false;
        }


        if ($handle)
            $this->loadLayout()->loadLayout($handle);
        else
            $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('b2b/order');
        }

        $block = $this->getLayout()->getBlock('order_view');

        if ($block) {
            $block->setRefererUrl($this->_getRefererUrl());
        }

        $this->renderLayout();
    }

    private function getOrderId()
    {
        $encoded = $this->getRequest()->getParam('order_id');
        return $this->commMessagingHelper->decrypt($encoded);
    }

    /**
     * Try to load valid order by order_id and register it
     *
     * @param int $orderId
     * @return bool
     */
    protected function _loadValidOrder($orderId = null)
    {
        $result = true;
        if (null === $orderId) {
            $orderId = $this->getOrderId();
        }


        //  if (null === $numLines) {
        $numLines = (int) $this->getRequest()->getParam('lines');
        // }
        if (!$orderId) {
            $this->customerSession->addError($this->__('The order you selected could not be retrieved.'));
            $this->_redirect('b2b/order');
            $result = false;
        } else {
            $order = $this->_getErpOrder($orderId, $numLines);
            if (!$order) {
                $this->customerSession->addError($this->__('The order you selected could not be retrieved.'));
                $this->_redirect('b2b/order');
                $result = false;
            } else {
                $this->registry->register('current_order', $order);
            }
        }
        return $result;
    }

    private function _getErpOrder($orderId, $numLines)
    {
        $accountNumber = $this->commMessagingHelper->getErpAccountNumber();
        $result = false;
        if (!$accountNumber) {
            $this->customerSession->addError($this->__('Sorry, we were unable to retrieve a valid account number.'));
        } else {
            $sod = $this->commMessageRequestSodFactory->create();
            $sod->setErpOrderNumber($orderId);
            $sod->setNumberOfLines($numLines);
            if ($sod->sendMessage()) {
                $result = $sod->getErpOrder();
            }
        }
        return $result;
    }

}
