<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Adminhtml\Arpayments;

use Magento\Backend\App\Action;

class Erpstatus extends \Epicor\Customerconnect\Controller\Adminhtml\Arpayments
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @param \Epicor\Comm\Controller\Adminhtml\Context $context
     * @param \Epicor\Customerconnect\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Epicor\Comm\Model\CustomerFactory $customer
     * @param \Magento\Framework\Session\GenericFactory $generic
     * @param \Epicor\Customerconnect\Model\ArPayment\Order\PaymentFactory $salesOrderPayment
     * @param \Epicor\Comm\Model\Erp\Mapping\PaymentFactory $commErpMappingPayment
     * @param \Epicor\Customerconnect\Model\Message\Request\CaapFactory $commMessageRequestCaapFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJson
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Customerconnect\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Framework\Session\GenericFactory $generic,
        \Epicor\Customerconnect\Model\ArPayment\Order\PaymentFactory $salesOrderPayment,
        \Epicor\Comm\Model\Erp\Mapping\PaymentFactory $commErpMappingPayment,
        \Epicor\Customerconnect\Model\Message\Request\CaapFactory $commMessageRequestCaapFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson
    ) {
        $this->resultJsonFactory = $resultJson;
        parent::__construct(
                $context, 
                $orderRepository,
                $backendAuthSession,
                $logger,
                $translateInline,
                $resultRawFactory,
                $customer,
                $generic,
                $salesOrderPayment,
                $commErpMappingPayment,
                $commMessageRequestCaapFactory
            );
    }

    /**
     * View order detail
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $arpaymentId  = $this->getRequest()->getParam('arpayment_id');
        $caapSent = $this->getRequest()->getParam('caap_sent');
        $this->changeErpstatus($arpaymentId, $caapSent);
        $this->messageManager->addSuccessMessage(__('Payment Erp Status changed'));
        $url = $this->getUrl('adminhtml/arpayments/view', array(
            'arpayment_id' => $arpaymentId, 
            'active_tab' => 'arpayment_tab_erppaymentinfo'
        ));
        $response = array('error' => false, 'success' => true, 'ajaxExpired' => true, 'ajaxRedirect' => $url);
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}
