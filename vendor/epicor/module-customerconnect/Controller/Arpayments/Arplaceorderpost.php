<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Arpayments;

class Arplaceorderpost extends \Epicor\Customerconnect\Controller\Arpayments
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
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession; 
    
    protected $resultRedirect;
    
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;    

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
        \Epicor\Customerconnect\Model\ArPayment\Session $checkoutSession,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    )
    {
        $this->customerconnectMessageRequestCaps = $customerconnectMessageRequestCaps;
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->layoutFactory = $layoutFactory;
        $this->checkoutSession = $checkoutSession;
        $this->resultRedirect = $context->getResultFactory();
        $this->response = $context->getResponse();
        $this->urlBuilder = $context->getUrl();
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
       $quote = $this->checkoutSession->getQuote();
       $order = $quote->submitQuote($quote);
             
       $redirectUrl = null;
       $this->checkoutSession->clearHelperData();       
       $this->checkoutSession
            ->setLastOrderId($order->getId())
            ->setRedirectUrl()
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());       
       if(!$order->getIncrementId()) {
            $html =''; 
            $this->getResponse()->setBody(json_encode(array('content' => $html,'error' => true)));
       } else {
          $html =$order->getIncrementId(); 
          $this->getResponse()->setBody(json_encode(array('content' => $html,'error' => false)));
       }
        
    }
    
    

    
    /**
     * @return \Magento\Framework\View\LayoutFactory
     */
    public function getLayoutFactory() {
        return $this->layoutFactory;
    }    
    
    

}