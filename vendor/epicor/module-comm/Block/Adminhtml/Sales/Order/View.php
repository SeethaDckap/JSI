<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Sales\Order;

/**
 * Adminhtml sales order view
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class View extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        array $data = []
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->registry = $registry;
        $this->backendAuthSession = $backendAuthSession;
        $this->_objectId = 'order_id';
        $this->_controller = 'sales_order';
        $this->_mode = 'view';

        parent::__construct(
            $context,
            $data
        );
        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->setId('sales_order_view');
        $order = $this->getOrder();

        if ($this->_isAllowedAction('edit') && $order->canEdit()) {
            $onclickJs = 'deleteConfirm(\''
                . __('Are you sure? This order will be canceled and a new one will be created instead')
                . '\', \'' . $this->getEditUrl() . '\');';
            $this->_addButton('order_edit', array(
                'label' => __('Edit'),
                'onclick' => $onclickJs,
            ));
            // see if order has non-editable products as items
            $nonEditableTypes = array_keys($this->getOrder()->getResource()->aggregateProductsByTypes(
            //M1 > M2 Translation Begin (Rule P2-5.6)
            /*$order->getId(), array_keys(Mage::getConfig()
                ->getNode('adminhtml/sales/order/create/available_product_types')
                ->asArray()*/

                $order->getId(), array_keys($this->scopeConfig
                ->getValue('adminhtml/sales/order/create/available_product_types')
                ->asArray()
            //M1 > M2 Translation End

                    ), false
            ));
            if ($nonEditableTypes) {
                $this->_updateButton('order_edit', 'onclick', 'if (!confirm(\'' .
                    //M1 > M2 Translation Begin (Rule 55)
                    //__('This order contains (%s) items and therefore cannot be edited through the admin interface at this time, if you wish to continue editing the (%s) items will be removed, the order will be canceled and a new order will be placed.', implode(', ', $nonEditableTypes), implode(', ', $nonEditableTypes)) . '\')) return false;' . $onclickJs
                    __('This order contains (%1) items and therefore cannot be edited through the admin interface at this time, if you wish to continue editing the (%2) items will be removed, the order will be canceled and a new order will be placed.', implode(', ', $nonEditableTypes), implode(', ', $nonEditableTypes)) . '\')) return false;' . $onclickJs
                    //M1 > M2 Translation End
                );
            }
        }

        if ($this->_isAllowedAction('cancel') && $order->canCancel()) {
            $message = __('Are you sure you want to cancel this order?');
            $this->_addButton('order_cancel', array(
                'label' => __('Cancel'),
                'onclick' => 'deleteConfirm(\'' . $message . '\', \'' . $this->getCancelUrl() . '\')',
            ));
        }

        if ($this->_isAllowedAction('emails') && !$order->isCanceled()) {
            $message = __('Are you sure you want to send order email to customer?');
            $this->addButton('send_notification', array(
                'label' => __('Send Email'),
                'onclick' => "confirmSetLocation('{$message}', '{$this->getEmailUrl()}')",
            ));
        }
        $creditMemosOfflineAllowed = $this->scopeConfig->isSetFlag('sales/order_credit_memo/options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($this->_isAllowedAction('creditmemo') && $order->canCreditmemo() && $creditMemosOfflineAllowed) {
            $message = __('This will create an offline refund. To create an online refund, open an invoice and create credit memo for it. Do you wish to proceed?');
            $onClick = "setLocation('{$this->getCreditmemoUrl()}')";
            if ($order->getPayment()->getMethodInstance()->isGateway()) {
                $onClick = "confirmSetLocation('{$message}', '{$this->getCreditmemoUrl()}')";
            }
            $this->_addButton('order_creditmemo', array(
                'label' => __('Credit Memo'),
                'onclick' => $onClick,
                'class' => 'go'
            ));
        }

        // invoice action intentionally
        if ($this->_isAllowedAction('invoice') && $order->canVoidPayment()) {
            $message = __('Are you sure you want to void the payment?');
            $this->addButton('void_payment', array(
                'label' => __('Void'),
                'onclick' => "confirmSetLocation('{$message}', '{$this->getVoidPaymentUrl()}')",
            ));
        }

        if ($this->_isAllowedAction('hold') && $order->canHold()) {
            $this->_addButton('order_hold', array(
                'label' => __('Hold'),
                'onclick' => 'setLocation(\'' . $this->getHoldUrl() . '\')',
            ));
        }

        if ($this->_isAllowedAction('unhold') && $order->canUnhold()) {
            $this->_addButton('order_unhold', array(
                'label' => __('Unhold'),
                'onclick' => 'setLocation(\'' . $this->getUnholdUrl() . '\')',
            ));
        }

        if ($this->_isAllowedAction('review_payment')) {
            if ($order->canReviewPayment()) {
                $message = __('Are you sure you want to accept this payment?');
                $this->_addButton('accept_payment', array(
                    'label' => __('Accept Payment'),
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('accept')}')",
                ));
                $message = __('Are you sure you want to deny this payment?');
                $this->_addButton('deny_payment', array(
                    'label' => __('Deny Payment'),
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('deny')}')",
                ));
            }
            if ($order->canFetchPaymentReviewUpdate()) {
                $this->_addButton('get_review_payment_update', array(
                    'label' => __('Get Payment Update'),
                    'onclick' => 'setLocation(\'' . $this->getReviewPaymentUrl('update') . '\')',
                ));
            }
        }

        if ($this->_isAllowedAction('invoice') && $order->canInvoice()) {
            $_label = $order->getForcedDoShipmentWithInvoice() ?
                __('Invoice and Ship') :
                __('Invoice');
            $this->_addButton('order_invoice', array(
                'label' => $_label,
                'onclick' => 'setLocation(\'' . $this->getInvoiceUrl() . '\')',
                'class' => 'go'
            ));
        }

        if ($this->_isAllowedAction('ship') && $order->canShip() && !$order->getForcedDoShipmentWithInvoice()) {
            $this->_addButton('order_ship', array(
                'label' => __('Ship'),
                'onclick' => 'setLocation(\'' . $this->getShipUrl() . '\')',
                'class' => 'go'
            ));
        }

        if ($this->_isAllowedAction('reorder') && $this->helper('sales/reorder')->isAllowed($order->getStore()) && $order->canReorderIgnoreSalable()
        ) {
            $this->_addButton('order_reorder', array(
                'label' => __('Reorder'),
                'onclick' => 'setLocation(\'' . $this->getReorderUrl() . '\')',
                'class' => 'go'
            ));
        }
    }

    /**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->registry->registry('sales_order');
    }

    /**
     * Retrieve Order Identifier
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getOrder()->getId();
    }

    public function getHeaderText()
    {
        if ($_extOrderId = $this->getOrder()->getExtOrderId()) {
            $_extOrderId = '[' . $_extOrderId . '] ';
        } else {
            $_extOrderId = '';
        }
        //M1 > M2 Translation Begin (Rule 55)
        //return __('Order # %s %s | %s', $this->getOrder()->getRealOrderId(), $_extOrderId, $this->formatDate($this->getOrder()->getCreatedAtDate(), 'medium', true));
        return __('Order # %1 %2 | %3', $this->getOrder()->getRealOrderId(), $_extOrderId, $this->formatDate($this->getOrder()->getCreatedAtDate(), 'medium', true));
        //M1 > M2 Translation End
    }

    public function getUrl($params = '', $params2 = array())
    {
        $params2['order_id'] = $this->getOrderId();
        return parent::getUrl($params, $params2);
    }

    public function getEditUrl()
    {
        return $this->getUrl('*/sales_order_edit/start');
    }

    public function getEmailUrl()
    {
        return $this->getUrl('*/*/email');
    }

    public function getCancelUrl()
    {
        return $this->getUrl('*/*/cancel');
    }

    public function getInvoiceUrl()
    {
        return $this->getUrl('*/sales_order_invoice/start');
    }

    public function getCreditmemoUrl()
    {
        return $this->getUrl('*/sales_order_creditmemo/start');
    }

    public function getHoldUrl()
    {
        return $this->getUrl('*/*/hold');
    }

    public function getUnholdUrl()
    {
        return $this->getUrl('*/*/unhold');
    }

    public function getShipUrl()
    {
        return $this->getUrl('*/sales_order_shipment/start');
    }

    public function getCommentUrl()
    {
        return $this->getUrl('*/*/comment');
    }

    public function getReorderUrl()
    {
        return $this->getUrl('*/sales_order_create/reorder');
    }

    /**
     * Payment void URL getter
     */
    public function getVoidPaymentUrl()
    {
        return $this->getUrl('*/*/voidPayment');
    }

    protected function _isAllowedAction($action)
    {
        return $this->backendAuthSession->isAllowed('sales/order/actions/' . $action);
    }

    /**
     * Return back url for view grid
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getOrder()->getBackUrl()) {
            return $this->getOrder()->getBackUrl();
        }

        return $this->getUrl('*/*/');
    }

    public function getReviewPaymentUrl($action)
    {
        return $this->getUrl('*/*/reviewPayment', array('action' => $action));
    }

//
//    /**
//     * Return URL for accept payment action
//     *
//     * @return string
//     */
//    public function getAcceptPaymentUrl()
//    {
//        return $this->getUrl('*/*/reviewPayment', array('action' => 'accept'));
//    }
//
//    /**
//     * Return URL for deny payment action
//     *
//     * @return string
//     */
//    public function getDenyPaymentUrl()
//    {
//        return $this->getUrl('*/*/reviewPayment', array('action' => 'deny'));
//    }
//
//    public function getPaymentReviewUpdateUrl()
//    {
//        return $this->getUrl('*/*/reviewPaymentUpdate');
//    }
}
