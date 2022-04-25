<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Arpayments;

class Updateinvoices extends \Epicor\Customerconnect\Controller\Arpayments
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

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     */
    private $serializer;  

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
        \Epicor\Customerconnect\Model\ArPayment\Session $checkoutSession,
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer
    )
    {
        $this->customerconnectMessageRequestCaps = $customerconnectMessageRequestCaps;
        $this->request = $request;
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->checkoutSession = $checkoutSession;
        $this->serializer = $serializer;
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
       try {
           $customAddressAllowed = $this->request->getParam('customAddressAllowed');
            $quote = $this->checkoutSession->getQuote();
            $quote_id= $quote->getId();
            $quote = $quote->load($quote_id);
            $invoiceinfo = $this->getRequest()->getParam('invoiceinfo');
            $request=[];
            if($invoiceinfo !="{}"){
                $invoiceinfo = json_decode($invoiceinfo, true);
               foreach($invoiceinfo as $invoice){
                   $request[$invoice['invoiceNo']]=[
                       'price'=>$invoice['userPaymentAmount'],
                       'additional_data'=>json_encode($invoice)
                    ];        
               }
            }
            $quote->addMultiInvoice($request);
            $quote->save();
            $paymentOnAccount = 0;
            if($this->getRequest()->getParam('paymentOnAccount') == 'true'){
                 $grandTotal = $this->getRequest()->getParam('allocatedAmount');
                 $quote->setGrandTotal($grandTotal);
                 $quote->setbaseGrandTotal($grandTotal);
                 $paymentOnAccount = 1;
            }else{
                $quote->collectTotals();
            }
            $allocated_amount = $this->getRequest()->getParam('allocatedAmount');
            $amountLeft = $this->getRequest()->getParam('amountLeft');
            $quote->setData('ecc_arpayments_allocated_amount',$allocated_amount);
            $quote->setData('ecc_arpayments_amountleft',$amountLeft);
            $quote->setData('ecc_arpayments_ispayment',$paymentOnAccount);
            $quote->save();   $this->getResponse()->setBody(json_encode(array('content' => '1','error' => false)));

       }catch (\Exception $e) {
             $html .= $e->getMessage();
            $this->getResponse()->setBody(json_encode(array('content' => $html,'error' => true)));
        }
    }
    

}
