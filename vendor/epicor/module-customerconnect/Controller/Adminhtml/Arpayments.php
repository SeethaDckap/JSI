<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Adminhtml;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ErpaccountController
 *
 * @author David.Wylie
 */
abstract class Arpayments extends \Epicor\Comm\Controller\Adminhtml\Generic
{
    /**
     * ACL ID
     *
     * @var string
     */
    protected $_aclId = 'Epicor_Customerconnect::arpayments';
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @var \Epicor\Customerconnect\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $_translateInline;
    
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;
    
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerfactory;
    
    /**
     *
     * @var \Magento\Framework\Session\GenericFactory 
     */
    protected $generic;
    
    /**
     *
     * @var \Epicor\Customerconnect\Model\ArPayment\Order\PaymentFactory 
     */
    protected $salesOrderPayment;
    
    /**
     *
     * @var \Epicor\Comm\Model\Erp\Mapping\PaymentFactory 
     */
    protected $commErpMappingPayment;
    
    /**
     *
     * @var \Epicor\Customerconnect\Model\Message\Request\CaapFactory 
     */
    protected $commMessageRequestCaapFactory;
    
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
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Customerconnect\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        LoggerInterface $logger,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Framework\Session\GenericFactory $generic,
        \Epicor\Customerconnect\Model\ArPayment\Order\PaymentFactory $salesOrderPayment,
        \Epicor\Comm\Model\Erp\Mapping\PaymentFactory $commErpMappingPayment,
        \Epicor\Customerconnect\Model\Message\Request\CaapFactory $commMessageRequestCaapFactory
    ) {
        $this->_coreRegistry = $context->getRegistry();
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->_translateInline = $translateInline;
        $this->resultRawFactory = $resultRawFactory;
        $this->customerfactory = $customer;
        $this->generic = $generic;
        $this->salesOrderPayment = $salesOrderPayment;
        $this->commErpMappingPayment = $commErpMappingPayment;
        $this->commMessageRequestCaapFactory = $commMessageRequestCaapFactory;
        parent::__construct($context, $backendAuthSession);
    }
    
    /**
     * Init layout, menu and breadcrumb
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Customerconnect::arpayments');
        $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        $resultPage->addBreadcrumb(__('AR Payments'), __('AR Payments'));
        return $resultPage;
    }
    
    /**
     * Initialize AR Payment model instance
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|false
     */
    protected function _initArpayment()
    {
        $id = $this->getRequest()->getParam('arpayment_id');
        try {
            $arpayment = $this->orderRepository->get($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->_coreRegistry->register('ar_sales_order', $arpayment);
        $this->_coreRegistry->register('current_ar_order', $arpayment);
        return $arpayment;
    }
    
    protected function changeErpstatus($arpaymentId, $status)
    {
        $caap_message = 'Payment Not Sent';
        $state        = '';
        switch ($status) {
            case 0:
                $caap_message = 'Manually set to : Payment Not Sent';
                $state        = 'processing';
                break;
            case 1:
                $caap_message = 'Manually set to : Payment Sent';
                break;
            case 3:
                $caap_message = 'Manually set to : Erp Error';
                break;
        }
        
        $arpayment = $this->orderRepository->get($arpaymentId);
        
        if ($arpayment->getEccCaapSent() != $status) {
            $this->_coreRegistry->register("offline_order_{$arpayment->getId()}", true);
            $arpayment->setEccCaapSent($status);
            $arpayment->setEccCaapMessage($caap_message);
            if (!empty($state)) {
                $arpayment->setState($state);
            }
            $arpayment->save();
            if($status !="3")
            {
                $this->sendCaap($arpayment);
            }
        }
        return;
    }
    
     
    /**
     * Send AR Payment CAAP Message to the  ERP
     * @param Varien_Event_Observer $observer
     * @return Epicor_Comm_Model_Observer
     */    
    public function sendCaap($arpayment)
    {
        $this->generic->create()->setSkipEvent(true);
        $customer = $this->customerfactory->create()->load($arpayment->getCustomerId());
        $customerGroupId = $customer->getGroupId();
        $this->_caap = $this->commMessageRequestCaapFactory->create();
        if ($arpayment->getPayment()->getMethod()) {
            $magentoCode = $this->salesOrderPayment->create()->load($arpayment->getId(), 'parent_id')->getMethod();
            $erpCode = $this->commErpMappingPayment->create()->load($magentoCode, 'magento_code')->getErpCode();
        }
        if ($this->_caap->isActive()) {
            $this->_caap->setOrder($arpayment);
            $this->_caap->setCustomer($customer);
            $this->_caap->setPromotions(false);
            $this->_caap->sendMessage();
            if (!$this->_caap->getConnectionSuccessful()) {        // if not successful connection has timedout
                $arpayment->setEccCaapSent(\Epicor\Customerconnect\Model\Message\Request\Caap::CAAP_STATUS_NOT_SENT);
                $arpayment->setEccCaapMessage('CAAP Failed -- Message Timed Out');
                $this->_caapTimedOut = true;
            }
            $this->_coreRegistry->unregister('last_log');
            $this->_coreRegistry->register('last_log', $this->_caap->getLog());            
        } else {
            $this->_coreRegistry->unregister('last_log');
            if ($this->_caap->getConnectionSuccessful()) {
                $arpayment->setEccCaapSent(Epicor_Customerconnect_Model_Message_Request_Caap::CAAP_STATUS_ERROR);
                $arpayment->setEccCaapMessage('CAAP Failed -- ' . $this->_caap->getStatusCode() );
            } else {
                $arpayment->setEccCaapMessage('CAAP Connection Failure');
            }
        }
        $arpayment->save();
        $this->_coreRegistry->unregister('SkipEvent');
    }
}
