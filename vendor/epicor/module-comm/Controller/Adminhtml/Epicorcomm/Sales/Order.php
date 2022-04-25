<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales;


abstract class Order extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;        
    
    /**
     * @var \Epicor\Comm\Helper\BsvAndGor
     */
    protected $bsvAndGorHelper;

    /**
     * @var string
     */
    public $changedStatus;


    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Helper\Data $commHelper,        
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Helper\BsvAndGor $bsvAndGorHelper)
    {
        $this->registry = $context->getRegistry();
        $this->commHelper = $commHelper;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->bsvAndGorHelper = $bsvAndGorHelper;

        parent::__construct($context, $backendAuthSession);
    }


    protected function _initPage()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb(__('Sales'), __('Sales'))
            ->_addBreadcrumb(__('Orders'), __('Orders'));
        return $this;
    }

    /**
     * @return []
     */
    public function getStatusMessages()
    {
        switch ($this->changedStatus) {
            case 0:
                $statusMessages['gor_message'] = 'Manually set to : Order Not Sent';
                $statusMessages['state'] = 'processing';
                break;
            case 1:
                $statusMessages['gor_message'] = 'Manually set to : Order Sent';
                break;
            case 3:
                $statusMessages['gor_message'] = 'Manually set to : Erp Error';
                break;
            case 4:
                $statusMessages['gor_message'] = 'Manually set to : Error - Retry Attempt Failure';
                break;
            case 5:
                $statusMessages['gor_message'] = 'Manually set to : Order Never Send';
                break;
            default:
                $statusMessages['gor_message'] = 'Order Not Sent';
                $statusMessages['state'] = '';
        }

        return $statusMessages;
    }

    /**
     * @param $order_id
     * @param $status
     * @throws \Exception
     */
    protected function changeErpstatus($order_id, $status)
    {
        $this->changedStatus = $status;
        $gor_message = $this->getStatusMessages()['gor_message'] ?? '';
        $state = $this->getStatusMessages()['state'] ?? '';

        $order = $this->salesOrderFactory->create()->load($order_id);

        if ($order->getEccGorSent() != $status) {
            if($this->registry->registry("isAdminGORForce")){
                $this->registry->unregister("isAdminGORForce");
            }            
            $this->registry->register("isAdminGORForce", true);
            $this->registry->register("offline_order_{$order->getId()}", true);
            if($status == 0){ //Reset Retry Count
                $order->setEccGorSentCount(0); 
            }
            $order->setEccGorSent($status);
            $order->setEccGorMessage($gor_message);
            if (!empty($state)) {
                $order->setState($state);
            }
            $order->save();
            $this->registry->unregister("isAdminGORForce");
            //Send BSV and GOR
            //$this->bsvAndGorHelper->SendOrderToErp($order);            
        }
    }

    /**
     *
     * @return \Epicor\Comm\Helper\Data
     */
    protected function _getHelper()
    {
        if (!$this->_helper)
            $this->_helper = $this->commHelper;
        return $this->_helper;
    }
}
