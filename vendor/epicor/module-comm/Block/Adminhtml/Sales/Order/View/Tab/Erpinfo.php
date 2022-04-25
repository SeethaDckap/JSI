<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Sales\Order\View\Tab;


class Erpinfo extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{


    protected $_chat = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commHelper = $commHelper;

        parent::__construct(
            $context,
            $data
        );
    }


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Comm::epicor_comm/sales/order/view/tab/erpinfo.phtml');
    }

    public function getTabLabel()
    {
        return __('ERP Order Information');
    }

    public function getTabTitle()
    {
        return __('ERP Order Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    public function getErpOrderNumber()
    {
        return $this->getOrder()->getEccErpOrderNumber() ? $this->getOrder()->getEccErpOrderNumber() : "-";
    }

    public function getManuallySet()
    {
        return strpos($this->getOrder()->getEccGorMessage(), 'Manually set to :') !== false;
    }

    public function getStatuses()
    {
        return array(
            '0' => 'Order Not Sent',
            '1' => 'Order Sent',
            '3' => 'Erp Error',
            '4' => 'Error - Retry Attempt Failure',
            '5' => 'Order Never Send',
        );
    }
    
    public function getGorSentCount()
    {
        return $this->getOrder()->getEccGorSentCount() ? $this->getOrder()->getEccGorSentCount() : __('N/A');
    }
    /*
     * GOR failure message
     * return @string
     */
    public function getGorFailureMsg()
    {
        $order = $this->getOrder();

        //only check for retry count or aged time limit if the order has been set to 'Error - Retry Attempt Failure'
        if($order->getEccGorSent() == 4) {
            $gorMessageReason = explode('---', $order->getEccGorMessage());
            if(isset($gorMessageReason[1])){
                return $gorMessageReason[1];
            }
        }
    }

}
