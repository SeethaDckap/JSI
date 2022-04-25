<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Arpayments;

class Addresspost extends \Epicor\Customerconnect\Controller\Arpayments
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

    /**
     * @var \Epicor\Customerconnect\Model\ArPayment\Quote\Address
     */
    protected $quoteAddress;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory
     */
    protected $erpaccountAddressFactory;  

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

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
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Epicor\Customerconnect\Model\ArPayment\Quote\AddressFactory $quoteAddress,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $erpaccountAddressFactory
    )
    {
        $this->customerconnectMessageRequestCaps = $customerconnectMessageRequestCaps;
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->layoutFactory = $layoutFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteAddress = $quoteAddress;
        $this->erpaccountAddressFactory = $erpaccountAddressFactory;
        $this->_customerSession = $customerSession;
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
        $customer = $this->_customerSession->getCustomer();
        $addressId = $this->getRequest()->getParam('erpInfo');
        $getName = $quote->getBillingAddress()->getFirstName();
        $quoteID = $quote->getId();
        $mode = $this->getRequest()->getParam('mode');
        $html='';
        if($addressId) {                    
            $billingaddressid = $addressId;
            $customerAddress = $this->erpaccountAddressFactory
                ->create()->load($billingaddressid);
            $cus_address = $customerAddress->toCustomerAddress($customer);
            $adressdata = $cus_address->getData();
            if ($customer->isSalesRep()) {
                $adressdata['customer_address_id']= $addressId;
            } else {
                $adressdata['customer_address_id']= $adressdata['entity_id'];
            }
            $adressdata['customer_notes']='erpaddress';
        }
        if($this->getRequest()->getParam('newAddress') == 'true'){
                $adressdata = $this->getRequest()->getParam('addressInfo');
            $adressdata['customer_notes'] = 'newaddress';
            $adressdata['customer_address_id'] = '';
        }
        $billingAddress = $this->quoteAddress->create();
        try{
         //   $quote->getBillingAddress()->delete();
        //   $quote->getShippingAddress()->delete();
            //$quote->getBillingAddress()->save();
            $billingAddress->setData($adressdata);

            $quote->setBillingAddress($billingAddress);
            $quote->setShippingAddress($billingAddress);
            $quote->save();
            $quote->getBillingAddress()->save();
            $quote->getShippingAddress()->save();
        
            $allItems = $quote->getBillingAddress();
            $addressHtml = $allItems->format('html');
            if($mode =="checkout") {
                $html = '<span id="chekout_address_html"><p>' . $addressHtml . '</p><span class="change_address" onclick="arPaymentsJs.addressCheckoutpopup(1)">Change Address</span><br><span>';                  
                $parentcontent = '<div class="address_block" id="address_block"><div class="address_label"><label for="allocate_amount">'
                        . '<h3 class="billingaddressheading">Card Holders Billing Address:</h3></label></div>'
                        . '<div id="landing_address_content" class="address_content">' . $addressHtml . '<br>'
                        . '<span class="change_address" onclick="arPaymentsJs.addOrUpdateAddress(1)" quote_id=' . $quoteID . '>'
                        . 'Change Address</span></div>';      
                $this->getResponse()->setBody(json_encode(array('content' => $html,'parentcontent' => $parentcontent,'error' => false)));
            } else {
                $html = '<div class="address_block" id="address_block"><div class="address_label"><label for="allocate_amount">'
                        . '<h3 class="billingaddressheading">Card Holders Billing Address:</h3></label></div>'
                        . '<div id="landing_address_content" class="address_content">' . $addressHtml . '<br>'
                        . '<span class="change_address" onclick="arPaymentsJs.addOrUpdateAddress(1)" quote_id=' . $quoteID . '>'
                        . 'Change Address</span></div>';   
                $this->getResponse()->setBody(json_encode(array('content' => $html,'error' => false)));
            }
            
         } catch (\Exception $e) {
             $html .= $e->getMessage();
            $this->getResponse()->setBody(json_encode(array('content' => $html,'error' => true)));
        }
        
    }
    
    /**
     * @return \Magento\Framework\View\LayoutFactory
     */
    public function getLayoutFactory() {
        return $this->layoutFactory;
    }    
    
    

}