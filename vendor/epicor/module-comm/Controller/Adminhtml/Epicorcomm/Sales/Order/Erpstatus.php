<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Order;

class Erpstatus extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Order
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Helper\BsvAndGor $bsvAndGorHelper)
    {
        $this->resultJsonFactory = $resultJson;
        parent::__construct($context, $commHelper, $salesOrderFactory, $backendAuthSession, $bsvAndGorHelper);
    }


    public function execute()
    {


        $order_id = $this->getRequest()->getParam('order_id');
        $gor_sent = $this->getRequest()->getParam('ecc_gor_sent');

        $this->changeErpstatus($order_id, $gor_sent);

        $this->messageManager->addSuccessMessage(__('Order Erp Status changed'));

        $url = $this->getUrl('sales/order/view', array('order_id' => $order_id, 'active_tab' => 'order_design_details'));
        $response = array('error' => false, 'success' => true, 'ajaxExpired' => true, 'ajaxRedirect' => $url);

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);

        return $resultJson;
    }

}
