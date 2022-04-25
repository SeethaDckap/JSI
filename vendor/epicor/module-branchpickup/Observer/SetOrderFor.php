<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer;

class SetOrderFor extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Epicor\BranchPickup\Helper\Branchpickup $branchPickupBranchpickupHelper,
        \Epicor\BranchPickup\Model\BranchpickupFactory $branchPickupBranchpickupFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper
    )
    {
        $this->customerSession = $customerSession;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->commHelper = $commHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
            $branchPickupHelper,
            $branchPickupBranchpickupHelper,
            $branchPickupBranchpickupFactory,
            $checkoutSession,
            $request,
            $storeManager,
            $response,
            $urlDecoder,
            $escaper,
            $messageManager
        );
    }

    /**
             * Url must start from base secure or base unsecure url
             */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
 
        $gor = $observer->getEvent()->getMessage();
        /* @var $gor Epicor_Comm_Model_Message_Upload_Gor */
        $order = $gor->getOrder();
        /* @var $order Epicor_Comm_Model_Order */
        $xml = $gor->getMessageArray();        
         
        $selectedBranch = $this->_helper->getSelectedBranch();
 
        if (strpos($order->getShippingMethod(),\Epicor\BranchPickup\Model\Carrier\Epicorbranchpickup::ECC_BRANCHPICKUP) !==false && ($selectedBranch)) {
    
            unset($xml['messages']['request']['body']['orderFor']);
            unset($xml['messages']['request']['body']['delivery']['deliveryAddress']);
            //M1 > M2 Translation Begin (Rule 58)
            //$isLoggedIn = Mage::helper('customer')->isLoggedIn();
            $isLoggedIn = $this->customerSession->isLoggedIn();
            //M1 > M2 Translation End
            $shippingName = null;
            if (!$isLoggedIn) {
                $shippingName = $order->getShippingAddress()->getName();
            }
            $xml['messages']['request']['body']['delivery']['deliveryAddress'] = $this->_helper->getOrderFor($selectedBranch, 1, $shippingName);
            $xml['messages']['request']['body']['orderFor'] = $this->_helper->getOrderFor($selectedBranch, null, $shippingName);
            
//            $customer = $this->customerSession->getCustomer();
//            if ($customer->isSalesRep() && $this->commHelper->isMasquerading()) {
//                 $shippingAddress = $order->getShippingAddress();
//                 $xml['messages']['request']['body']['orderFor']['contactName']=$this->commMessagingHelper->stripNonPrintableChars($shippingAddress->getName());
//                $xml['messages']['request']['body']['delivery']['deliveryAddress']['contactName']=$this->commMessagingHelper->stripNonPrintableChars($shippingAddress->getName());
//            }
            
            $xml['messages']['request']['body']['storeCollect'] = $selectedBranch;
            $gor->setMessageArray($xml);
            
    
             
        }

    }

}