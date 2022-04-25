<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Arpayments;

class Printpayment extends \Epicor\Customerconnect\Controller\Arpayments
{
    /**
     * @var \Epicor\Customerconnect\Model\ArPayment\OrderFactory
     */
    protected $arpaymentOrder;
   
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\CacheInterface $cache,
        \Epicor\Customerconnect\Model\ArPayment\OrderFactory $arpaymentOrder,
        \Magento\Framework\Registry $registry
    ) {
        $this->commHelper = $commHelper;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->commonAccessGroupCustomerFactory = $commonAccessGroupCustomerFactory;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->generic = $generic;
        $this->cache = $cache;
        $this->arpaymentOrder = $arpaymentOrder;
        $this->registry = $registry;
        
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $commHelper,
            $customerResourceModelCustomerCollectionFactory,
            $commonAccessGroupCustomerFactory,
            $customerconnectHelper,
            $generic,
            $cache
        );
    }
    
    public function execute() {
        $initOrder = $this->_initOrder();
        if($initOrder) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
           $resultPage = $this->resultPageFactory->create();
           $resultPage->addHandle('print');
           return $resultPage;
        }
    }
    
     /**
     * Initialize Arpayment order model instance
     *
     * @return Epicor\Customerconnect\Model\Arpayment\Order || false
     */
    protected function _initOrder()
    {
       $id  = $this->getRequest()->getParam('order_id'); 
       $order = $this->arpaymentOrder->create()->load($id);
       $customer_id = $this->customerSession->getCustomer()->getId();
        if ((!$order->getId()) || ($order->getCustomerId() != $customer_id)) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_redirect('*/*/');
            return false;
        }
         $this->registry->register('current_order', $order);
         return true;
    }
    
}
