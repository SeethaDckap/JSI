<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model;

use Magento\Framework\Stdlib\DateTime\DateTime;

class Arpayments extends \Magento\Framework\Model\AbstractModel 
{

    protected $customerSession;


    protected $tokenCollectionFactory;    

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteQuoteFactory;    

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;    
    
    
    /**
     * @var \Epicor\Comm\Helper\DataFactory
     */
    protected $commHelper;    
    
    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory
     */
    protected $commResourceCustomerErpaccountAddressCollectionFactory;    
    
    protected $quoteAddress;
    
    protected $arpaymentsHelper;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;    
    
    /**
     * @var string
     */
    protected $productType = \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL;    

    protected $arpaymentName = "ECC AR Payment Virtual Product - do no delete or modify";

    protected $arpaymentSku ="Ar Payments";

    protected $arpaymentDefQty = 100000000000000000;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $date;     
    
    
    protected $quoteRepository;
    
    
    protected $CheckoutSession;
    
    protected $checkoutCartHelper;

    protected $stockRegistry;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory $commResourceCustomerErpaccountAddressCollectionFactory,            
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\DataFactory $commHelper,
        \Epicor\Customerconnect\Model\ArPayment\Quote\AddressFactory $quoteAddress,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        DateTime $date,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Checkout\Helper\Cart $checkoutCartHelper,
        \Magento\Checkout\Model\Session $CheckoutSession,
        \Magento\Quote\Model\Quote\ItemFactory $quoteQuoteItemFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Model\SessionFactory  $customerSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->commHelper = $commHelper;
        $this->urlBuilder = $urlBuilder;
        $this->customerSession = $customerSession;
        $this->quoteAddress = $quoteAddress;
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->commResourceCustomerErpaccountAddressCollectionFactory = $commResourceCustomerErpaccountAddressCollectionFactory;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;       
        $this->date         = $date;
        $this->quoteRepository = $quoteRepository;
        $this->CheckoutSession = $CheckoutSession;
        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->stockRegistry = $stockRegistry;
        $this->indexerRegistry = $indexerRegistry;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }
    
    /**
     * Getting the Invoice Address from ERP Account
     * 
     * return address
     */
    public function getErpAddressList() {
        $data= array();
        $type = "customer";
        $customer = $this->customerSession->create()->getCustomer();
        $name= $customer->getName();
        $helper = $this->commHelper->create();
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $helper->getErpAccountInfo(null, $type);  
        $erp_group_addresses = array();
        $addressColl = $this->commResourceCustomerErpaccountAddressCollectionFactory->create();
        /* @var $addressColl Epicor_Comm_Model_Resource_Customer_Erpaccount_Address_Collection */
        $addressColl->addFieldToFilter('erp_customer_group_code', $erpAccount->getErpCode()); 
        $addressColl->addFieldToFilter('is_invoice', 1); 
        $getLocationData = $addressColl;
        foreach ($getLocationData as $addressData) {
            $streetaddress = null;
            if ($addressData->getAddress1()) {
                $streetaddress .= $addressData->getAddress1();
            }if ($addressData->getAddress2()) {
                $streetaddress .= ',' . $addressData->getAddress2();
            }
            if ($addressData->getAddress3()) {
                $streetaddress .= ',' . $addressData->getAddress3();
            }
            $address = $streetaddress;
            $getName   = $this->split_name($name);
            $firstname = $getName[0];
            $lastname  = ($getName[1]) ? $getName[1] : ',';            
            $details = $firstname ." ".$lastname ." " .  $streetaddress . ',' . $addressData->getCity() . ',' . $addressData->getCounty() . ',' . $addressData->getPostcode() . ',' . $addressData->getCountry();
            $data[] = array('address_id' => $addressData->getEntityId(), 'details' => $details);
        }
        
        return $data;        
    }    
    
    public function split_name($name) {
        $name = trim($name);
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim( preg_replace('#'.$last_name.'#', '', $name ) );
        return array($first_name, $last_name);
    }    
    
    /**
     * Check AR Payments is Active Or Not
     */
    public function checkArpaymentsActive()
    {
        $customer = $this->customerSession->create()->getCustomer();
        $commHelper =$this->commHelper->create();
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        $checkErp   = $erpAccount->getIsArpaymentsAllowed();
        $checkCapsEnabled = $this->scopeConfig->getValue('customerconnect_enabled_messages/CAPS_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!$checkCapsEnabled) {
            return false;
        }
        if ($checkErp == 1 || $checkErp == 3) {
            $allow = true;
        } elseif ($checkErp == 0) {
            $allow = false;
        } elseif ($checkErp == 2) {
            $allow =  $this->scopeConfig->getValue('customerconnect_enabled_messages/CAPS_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return $allow;
    }
    
    
    public function getErpAccountAddress($quote)
    {
        $commHelper = $this->commHelper->create();
        /* @var $commHelper Epicor_Comm_Helper_Data */
        $erpAccountInfo = $commHelper->getErpAccountInfo();
        /* @var $erpAccountInfo Epicor_Comm_Model_Customer_Erpaccount */
        $defaultBillingAddressCode = $erpAccountInfo->getDefaultInvoiceAddressCode();
        $billingAddress = $erpAccountInfo->getAddress($defaultBillingAddressCode);
        $customer = $this->customerSession->create()->getCustomer();
        if ($billingAddress) {
            $erpAddress = $billingAddress->toCustomerAddress($customer, $erpAccountInfo->getId());
            $billingAddressUpdate = $this->quoteAddress->create();
            $adressdata = $erpAddress->getData();
            if ($customer->isSalesRep()) {
                $adressdata['customer_address_id'] = $billingAddress->getData('entity_id');
            } else {
                $adressdata['customer_address_id']= $adressdata['entity_id'];
            }
            $adressdata['customer_notes'] = 'erpaddress';
            $billingAddressUpdate->setData($adressdata);
            if(!empty($billingAddressUpdate)) {
                $quote->setBillingAddress($billingAddressUpdate);
                $quote->setShippingAddress($billingAddressUpdate);
                $quote->save();
                $quote->getBillingAddress()->save();
                $quote->getShippingAddress()->save();  
            }
        }        
        
    }    
    
    
    /**
     * Updating the Magento Arpayments Quote from Arpayments Quote Table
     */    
    public function updateQuote($cartId) {
        $resource = $this->arpaymentsHelper->getResourceConnection();
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('quote');  
        $arQuote = $this->arpaymentsHelper->getArpaymentsQuote();
        $storeId = $this->storeManager->getStore()->getId();
        $data = [
            'created_at' => $this->date->gmtDate(),
            'updated_at' => $this->date->gmtDate(),
            'store_id' => $storeId,
            'is_active' => 1,
            'arpayments_quote' => 1,
            'items_count' =>1,
            'items_qty' => 1,
            'is_virtual' => 1,
            'grand_total' => $arQuote->getGrandTotal(),
            'base_grand_total' => $arQuote->getGrandTotal(),
            'customer_email' => $arQuote->getCustomerEmail(),
            'customer_id'=>NULL,
            'customer_firstname' => $arQuote->getCustomerFirstname(),
            'customer_lastname' => $arQuote->getCustomerLastname(),
            'subtotal' => $arQuote->getGrandTotal(),
            'base_subtotal' => $arQuote->getGrandTotal(),
            'subtotal_with_discount' => $arQuote->getGrandTotal(),
            'base_subtotal_with_discount' => $arQuote->getGrandTotal(),
            'customer_is_guest' => 1,
            'checkout_method' => 'guest'
        ];        
        $cartIdName = "entity_id";
        $resource->getConnection()->update($tableName, $data,[$cartIdName . '= ?' => $cartId]);
        $this->clearItemsInCart();
    } 
    
    /**
     * Adding a Temporary Virtual Product in Quote
     */     
    public function addProduct($cartId) {
        //check whether the virtual products is present
        $checkProduct = $this->checkProductPresent();
        //If the product is not there then create it
        if(!$checkProduct) {
          $checkProduct =  $this->insertProducts();
        } else {
            try {
                $stockItem = $this->stockRegistry->getStockItemBySku($this->arpaymentSku);
                $stockItem->setQty($this->arpaymentDefQty);
                $stockItem->setIsInStock(1);
                $this->stockRegistry->updateStockItemBySku($this->arpaymentSku, $stockItem);
            } catch (\Exception $e) {
                $this->_logger->info($e->getMessage());
            }
        }
        $arQuote = $this->arpaymentsHelper->getArpaymentsQuote();
        $this->saveProducts($cartId,$checkProduct,$arQuote);
    }  
    
    
    /**
     * Clearing all the items in the Magento Ar payments quote
     */    
    public function clearItemsInCart() {
            $checkoutSession = $this->CheckoutSession;
            $cartHelper = $this->checkoutCartHelper;
            $allItems = $checkoutSession->getQuote()->getAllVisibleItems();
            foreach ($allItems as $item) 
            {
                    $cartItemId = $item->getItemId();
                    $cartHelper->getCart()->removeItem($cartItemId)->save();
            }
    }

    /**
     * Saving the Product
     */
    public function saveProducts($cartId,$checkProduct,$arQuote) {
        $product = $this->productFactory->create()->load($checkProduct);
        $params = [
              "product"=>$checkProduct,
              "qty"=> "1",
                'price' => $arQuote->getGrandTotal(),
                'base_price' => $arQuote->getGrandTotal(),
                'custom_price' =>$arQuote->getGrandTotal(),
                'row_total' => $arQuote->getGrandTotal(),
                'base_row_total' => $arQuote->getGrandTotal()
          ];
        $request = new \Magento\Framework\DataObject();
        $request->setData($params);
        // here quote is created for customer
        $quote = $this->quoteRepository->get($cartId);
        /** @var $quote  \Epicor\Comm\Model\Quote */
        $quote->addProduct($product, $request);
        $quote->setCustomerId(null);
        foreach ($quote->getAllItems() as $arItems) {
           $arItems->setCustomPrice($arQuote->getGrandTotal());
           $arItems->setOriginalCustomPrice($arQuote->getGrandTotal());  
           $arItems->getProduct()->setIsSuperMode(true);
           $arItems->save();
           $arItems->setQty(1);
           $arItems->setOriginalCustomPrice($arQuote->getGrandTotal()); 
        }   
        $quote->setTotalsCollectedFlag(false)->collectTotals(); 
        $quote->getPayment()->setPoNumber("ARPAYMENTS");
        $quote->setCustomerId(null);
        $quote->setArpaymentsQuote(1);
        $this->quoteRepository->save($quote);

        if($product && $product->getName() != $this->arpaymentName) {
            try{
                $product->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID)->setName($this->arpaymentName);
                $product->save();
            } catch (\Exception $e) {
                $this->_logger->info($e->getMessage());
            }
        }
    }    
    
    
    /**
     * Copying the Arpayments table quote address to Magento Arpayments Quote
     */
    public function addShippingBilling($cartId) {
        $resource = $this->arpaymentsHelper->getResourceConnection();
        $arQuote = $this->arpaymentsHelper->getArpaymentsQuote();
        $arBillingQuote = $arQuote->getBillingAddress();
        $arShippingQuote = $arQuote->getShippingAddress();        
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('quote_address'); 
        $BillingStreet  = $arBillingQuote->getStreet();
        $shippingStreet = $arShippingQuote->getStreet();
        $explodeBilling  =  implode(',', $BillingStreet);
        $explodeShipping  =  implode(',', $shippingStreet);
        $cartIdName ="quote_id";
        $deleteEntries = $resource->getConnection()->delete($tableName, [$cartIdName . '= ?' => $cartId]);
        $totals = $arQuote->getGrandTotal();
        $dataBilling[] = [
            'created_at' => $this->date->gmtDate(),
            'updated_at' => $this->date->gmtDate(),
            'quote_id' => $cartId,
            'address_type' =>  'billing',
            'email' => $arBillingQuote->getEmail(),
            'firstname' => $arBillingQuote->getFirstname(),
            'lastname' => $arBillingQuote->getLastname(),
            'company' => $arBillingQuote->getCompany(),
            'street' =>$explodeBilling ,
            'city' =>$arBillingQuote->getCity(),
            'region' =>$arBillingQuote->getRegion(),
            'region_id' =>$arBillingQuote->getRegionId(),
            'postcode' => $arBillingQuote->getPostcode(),
            'country_id' => $arBillingQuote->getCountryId(),
            'telephone' =>$arBillingQuote->getTelephone(),
            'fax' => $arBillingQuote->getFax(),
            'customer_notes' => $arBillingQuote->getCustomerNotes(),
            'subtotal' => $totals,
            'same_as_billing' => 1,
            'customer_id' => $arQuote->getCustomerId(),
            'base_subtotal' => $totals,
            'subtotal_with_discount' => $totals,
            'base_subtotal_with_discount' => $totals,
            'grand_total' => $totals,
            'base_grand_total' => $totals,
            'email'=>$arQuote->getCustomerEmail()
        ];

        $dataBilling[] = [
            'created_at' => $this->date->gmtDate(),
            'updated_at' => $this->date->gmtDate(),
            'quote_id' => $cartId,
            'address_type' =>  'shipping',
            'same_as_billing' => 1,
            'customer_id' => $arQuote->getCustomerId(),
            'email' => $arBillingQuote->getEmail(),
            'firstname' => $arBillingQuote->getFirstname(),
            'lastname' => $arBillingQuote->getLastname(),
            'company' => $arBillingQuote->getCompany(),
            'street' =>$explodeBilling ,
            'city' =>$arBillingQuote->getCity(),
            'region' =>$arBillingQuote->getRegion(),
            'region_id' =>$arBillingQuote->getRegionId(),
            'postcode' => $arBillingQuote->getPostcode(),
            'country_id' => $arBillingQuote->getCountryId(),
            'telephone' =>$arBillingQuote->getTelephone(),
            'fax' => $arBillingQuote->getFax(),
            'customer_notes' => $arBillingQuote->getCustomerNotes(),
            'subtotal' => $totals,
            'base_subtotal' => $totals,
            'subtotal_with_discount' => $totals,
            'base_subtotal_with_discount' => $totals,
            'grand_total' => $totals,
            'base_grand_total' => $totals,
            'email'=>$arQuote->getCustomerEmail()
        ];   
        $resource->getConnection()->insertMultiple($tableName, $dataBilling);
        $quote = $this->quoteRepository->get($cartId);
        $quote->setTotalsCollectedFlag(false)->collectTotals(); 
        $quote->setCustomerId(null);
        $this->quoteRepository->save($quote);      
   }  
    
    /**
     * Checking whether the product is there or not
     */
    public function checkProductPresent() {
        $prodObj = $this->productFactory->create();
        $productId = $prodObj->getIdBySku($this->arpaymentSku);
        return $productId;
    }
    
    
    /**
     * Insert the Products into the Magento 2 catalog Table
     */    
    public function insertProducts() {
        $data = [
            'name' => $this->arpaymentName,
            'sku'  => $this->arpaymentSku,
            'price' => 1,
            'weight' => 0
        ];
        $attributeSetId = 4; //Attribute set default
        $product = $this->productFactory->create();
        $product->setData($data);
        $product
            ->setTypeId($this->productType)
            ->setAttributeSetId($attributeSetId)
            ->setWebsiteIds([$this->storeManager->getDefaultStoreView()->getWebsiteId()])
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setStockData(['is_in_stock' => 1, 'manage_stock' => 0, 'qty' => $this->arpaymentDefQty])
            ->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
        $product->save();

        //Reindex AR Payment new added product
        $this->reindexArProduct($product);

        return $product->getId();
    }
    
    
    /**
     * Clearing the Session Storage
     */     
    public function clearStorage() {
        $checkoutSession = $this->CheckoutSession->clearStorage();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    public function reindexArProduct(\Magento\Catalog\Model\Product $product)
    {
        $productCategoryIndexer = $this->indexerRegistry->get('cataloginventory_stock');
        if ($productCategoryIndexer->isScheduled()) {
            $productCategoryIndexer->reindexRow($product->getId());//
        }
    }


}