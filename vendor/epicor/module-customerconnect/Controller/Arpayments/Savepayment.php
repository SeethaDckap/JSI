<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Arpayments;

class Savepayment extends \Epicor\Customerconnect\Controller\Arpayments
{

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuad
     */
    protected $customerconnectMessageRequestCuad; 

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;
    
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;    

    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;  

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
        \Epicor\Customerconnect\Model\Message\Request\Caps $customerconnectMessageRequestCaps,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Model\ArPayment\Session $checkoutSession
    )
    {
        $this->customerconnectMessageRequestCaps = $customerconnectMessageRequestCaps;
        $this->request = $request;
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->checkoutSession = $checkoutSession;
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

    /**
     * Index action
     */
    public function execute()
    {
       try{
           $paymentmethod = $this->request->getParam('payment');
       
            $quote = $this->checkoutSession->getQuote();
                    //set Payment Method
            $quote->setPaymentMethod($paymentmethod); //payment method
            $quote->setInventoryProcessed(false); //not effetc inventory 
            // Set Sales Order Payment
            $quote->getPayment()->importData(['method' => $paymentmethod]);
            $quote->getPayment()->save();
            $this->getResponse()->setBody(json_encode(array('error' => false)));
        } catch (Exception $e) {
             $html .= $e->getMessage();
            $this->getResponse()->setBody(json_encode(array('content' => $html,'error' => true)));   

       }
    }

}