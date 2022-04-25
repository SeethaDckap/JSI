<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Arpayments;



class Archeckout extends \Magento\Framework\App\Action\Action
{
    
    protected $_storeManager;
    protected $_product;
    protected $cartRepositoryInterface;
    protected $cartManagementInterface;
    protected $customerFactory;
    protected $customerRepository;
    protected $order;
    
    /**
     * TODO: MAGETWO-34827: unused object?
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession; 
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;   
    
    
    /**
     * @var \Epicor\Lists\Helper\Session
     */
    protected $listsSessionHelper;   
    
    
    protected $quoteFactory;
    
    protected $arPaymentsModel;
    
    
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,        
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Epicor\Lists\Helper\Session $listsSessionHelper,
        \Epicor\Customerconnect\Model\Arpayments $arPaymentsModel,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\Order $order
    ) {
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->quoteRepository = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->order = $order;
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->listsSessionHelper = $listsSessionHelper;
        $this->quoteFactory = $quoteFactory;
        $this->arPaymentsModel = $arPaymentsModel;
        parent::__construct($context);
    }    

    /**
     * Checkout page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $sessionHelper = $this->listsSessionHelper;
        $arPaymentsSession = $sessionHelper->getValue('ecc_arpayments_quote');
        $store = $this->_storeManager->getStore();
        $customer = $this->_customerSession->getCustomer();
        $customerId = $customer->getId();        
        try {
            $cart = $this->quoteRepository->getForCustomer($customerId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            try {
                $this->cartManagementInterface->createEmptyCartForCustomer($customerId);
            } catch (\Exception $e) {
              //If no quote was there then it will create a quote for customer 
              $customerCart = $this->quoteFactory->create();
              $customerCart->setCustomerId($customerId);
              $customerCart->setStore($store); // Set Store
              $customerCart->save();
            }
        }      
        //If Arpayments session was there then it will load the value from session
        if($arPaymentsSession) {
            $quote = $this->quoteRepository->get($arPaymentsSession);
            //If the arpayments quote in the session was inactive, then again create a quote
            if(!$quote->getIsActive()) {
                $cartId = $this->quoteFactory->create();
                //For the temporary arpayment quote, Dont set customer id
                $cartId->setCustomerId(NULL); // Set Customer as null
                $cartId->setStore($store); // Set Store
                $cartId->save();
                $arPaymentsSession = $cartId->getId();
                $sessionHelper->setValue('ecc_arpayments_quote', $cartId->getId());                
            }
        } else {
            //If the Ar payments session was not there then it will create a separate quote id
            $cartId = $this->quoteFactory->create();
            //For the temporary arpayment quote, Dont set customer id
            $cartId->setCustomerId(NULL); // Set Customer as null
            $cartId->setStore($store); // Set Store
            $cartId->save();
            $arPaymentsSession = $cartId->getId();
            $sessionHelper->setValue('ecc_arpayments_quote', $cartId->getId());
            $this->getOnepage()->initCheckout();
        }
        $this->arPaymentsModel->clearStorage();
        //Update the informations in the quote from Ar payments quote table
        $addQuote   = $this->arPaymentsModel->updateQuote($arPaymentsSession);
        $addProduct = $this->arPaymentsModel->addProduct($arPaymentsSession);  
        $addAddress = $this->arPaymentsModel->addShippingBilling($arPaymentsSession);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->unsetElement('header.container');
        $resultPage->getLayout()->unsetElement('footer-container');
        $resultPage->getConfig()->getTitle()->set(__('Checkout'));
        return $resultPage;
    }
    
    
    /**
     * Get one page checkout model
     *
     * @return \Magento\Checkout\Model\Type\Onepage
     * @codeCoverageIgnore
     */
    public function getOnepage()
    {
        return $this->_objectManager->get(\Magento\Checkout\Model\Type\Onepage::class);
    }


    
}