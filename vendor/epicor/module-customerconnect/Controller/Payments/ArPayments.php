<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Payments;

class ArPayments extends \Epicor\Customerconnect\Controller\Payments
{

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cups
     */
    protected $customerconnectMessageRequestCups;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Epicor\Customerconnect\Model\ArPayment\Quote
     */
    protected $arSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Epicor\Customerconnect\Model\ArPayment\Quote\Address
     */
    protected $quoteAddress;

    /**
     * @var Address\Renderer
     */
    protected $addressRenderer;

    /**
     * Address repository
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * Transaction
     *
     * @var \Epicor\Customerconnect\Api\Data\TransactionInterface
     */
    protected $transaction;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Model\Message\Request\Cups $customerconnectMessageRequestCups,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Customerconnect\Model\ArPayment\Session $arSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Epicor\Customerconnect\Model\ArPayment\Quote\AddressFactory $quoteAddress,
        \Epicor\Customerconnect\Model\ArPayment\Order\Address\Renderer $addressRenderer,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Epicor\Customerconnect\Api\Data\TransactionInterface $transaction
    )
    {
        $this->customerconnectMessageRequestCups = $customerconnectMessageRequestCups;
        $this->generic = $generic;
        $this->arSession = $arSession;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->quoteAddress = $quoteAddress;
        $this->addressRenderer = $addressRenderer;
        $this->addressRepository = $addressRepository;
        $this->transaction = $transaction;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    /**
     * Index action
     */
    public function execute()
    {
        $customer = $this->customerSession->getCustomer();
        //Get Quote
        $quote = $this->arSession->getQuote();
         
         //add Item
         $request['pradeep3']=['price'=>100];
         $request['pradeep4']=['price'=>100];
         $request['pradeep5']=['price'=>100];         
         $quote->addMultiInvoice($request);
//         $request['price']=500;
//         $request['custom_price']=501;
//         $quote->addInvoice('pradeep1',$request);
         $quote->collectTotals()->save();
          
         //Add New address
         $address = $this->quoteAddress->create();
         $adressdata=[
                'save_in_address_book' => '0',
                'email' => 'p@example.com',
                'prefix' => '',
                'firstname' => 'Pradeep Contact For',
                'middlename' => 'middlename',
                'lastname' => 'lastname',
                'suffix' => '',
                'company' => 'pradepcompany',
                'street' => '495 SouthRd',
                 'city' =>'gggg',
                 'region' => 'Wisconsin' ,
                 'region_id' => '64',
                 'postcode' => '55128',
                 'country_id' => 'US',
                 'telephone' => '612-443-9087',
                 'fax' => '612-443-6843',
                 'ecc_mobile_number' => '',
                ];
        $address->setData($adressdata);
        $quote->setShippingAddress($address);
        $quote->getShippingAddress()->save();
        
//        $billingaddressid = 929;
//        $customerAddress = $this->addressRepository->getById($billingaddressid);
//        $billingAddress = $this->quoteAddress->create();
//        $billingAddress->importCustomerAddressData($customerAddress);
//        
        $quote->setBillingAddress($address);
        $quote->getBillingAddress()->save();
                    
         
         //payment
        echo $quote->getPayment()->getMethod();
    
        //set Payment Method
        $quote->setPaymentMethod('checkmo'); //payment method
        $quote->setInventoryProcessed(false); //not effetc inventory
 
        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => 'checkmo']);
        $quote->getPayment()->save();
        
        //Delete Item
        
//        $item = $quote->getItemByInvoice('pradeep3');
//        if($item){
//            $item->delete();           
//        }
        $quote->save(); //Now Save quote and your quote is ready
        echo '<br>Quote Items<br><br>';
        $quote_id= $quote->getId();
        //$quote = $this->arquote->create();
        $quote = $quote->load($quote_id);
        //get Items 
        foreach ($quote->getAllItems() as $item) {
            echo $item->getSku().'<br>';
        }
         echo '<br>Quote Grand Total<br><br>';
        echo $quote->getGrandTotal();
        
         echo '<br>Quote Billing Address <br><br>';
        echo $this->addressRenderer->format($quote->getBillingAddress(), 'html');
        
         echo '<br>Quote Shipping Address <br><br>';
        echo $this->addressRenderer->format($quote->getShippingAddress(), 'html');

        //Order
        $order = $quote->submitQuote($quote);
        echo '<br>Order Items<br><br>';
        foreach ($order->getAllItems() as $item) {
            echo $item->getSku().'<br>';
        }
        
         echo '<br>Order Billing Address <br><br>';
        echo $this->addressRenderer->format($order->getBillingAddress(), 'html');
        
         echo '<br>Order Shipping Address <br><br>';
        echo $this->addressRenderer->format($order->getShippingAddress(), 'html');
        echo $order->getStatus();
        
        echo '<br>'; print_r($order->getIncrementId());
        
        //Comments
        $order->addStatusHistoryComment('Testing')->save();
        
        //transaction
        
        $transaction=$this->transaction;
        $transaction->setOrder($order);
        $transaction->setTxnId(rand())->save();
        
        echo '<br>Transaction TxnId<br><br>';
        echo $transaction->getTxnId();
        //$order->save();
        echo '<br>END<br>';
        exit;
    }

}
