<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Manage;

class SaveDuplicate extends \Epicor\Quotes\Controller\Manage
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Quotes\Model\ResourceModel\Quote\Product\CollectionFactory
     */
    protected $quotesResourceQuoteProductCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Quotes\Model\ResourceModel\Quote\Note\CollectionFactory
     */
    protected $quotesResourceQuoteNoteCollectionFactory;

    /**
     * @var \Epicor\Quotes\Model\Quote\ProductFactory
     */
    protected $quotesQuoteProductFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /*
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Quotes\Model\ResourceModel\Quote\Product\CollectionFactory $quotesResourceQuoteProductCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Quotes\Model\ResourceModel\Quote\Note\CollectionFactory $quotesResourceQuoteNoteCollectionFactory,
        \Epicor\Quotes\Model\Quote\ProductFactory $quotesQuoteProductFactory,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->quotesResourceQuoteProductCollectionFactory = $quotesResourceQuoteProductCollectionFactory;
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->request = $request;
        $this->quotesResourceQuoteNoteCollectionFactory = $quotesResourceQuoteNoteCollectionFactory;
        $this->quotesQuoteProductFactory = $quotesQuoteProductFactory;
        $this->generic = $generic;
        $this->checkoutSession = $checkoutSession;
        parent::__construct(
            $context
        );
    }
    */
      public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
       \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Quotes\Model\ResourceModel\Quote\Product\CollectionFactory $quotesResourceQuoteProductCollectionFactory,
        \Epicor\Quotes\Model\ResourceModel\Quote\Note\CollectionFactory $quotesResourceQuoteNoteCollectionFactory,
        \Epicor\Quotes\Model\Quote\ProductFactory $quotesQuoteProductFactory,
          \Magento\Framework\App\Request\Http $request
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->commHelper = $commHelper;
        $this->storeManager = $storeManager;
        $this->quotesResourceQuoteProductCollectionFactory = $quotesResourceQuoteProductCollectionFactory;
        $this->quotesResourceQuoteNoteCollectionFactory = $quotesResourceQuoteNoteCollectionFactory;
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->quotesQuoteProductFactory = $quotesQuoteProductFactory;
          $this->request = $request;
         parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    public function execute()
     {
        $success = ('Quote was duplicated and submitted for review.');
        $error = ('Quote failed to be submitted for review.');
        $currentQuoteId = $this->getRequest()->getParam('id');
//        $currentQuote = Mage::getModel('quotes/quote')->load($currentQuoteId);
        $currentQuoteProductlines = $this->quotesResourceQuoteProductCollectionFactory->create();
        $currentQuoteProductlines->addFieldToFilter('quote_id', $currentQuoteId);

        /* @var $currentQuoteProductlines Mage_Core_Model_Resource_Collection_Generic */

        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */
        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        if (!$customerSession->authenticate($this)) {
            return;
        }

        try {
            $commHelper = $this->commHelper;
            /* @var $commHelper Epicor_Comm_Helper_Data */
            $erpAccount = $commHelper->getErpAccountInfo();
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
            $daysTillExpired = $this->scopeConfig->getValue('epicor_quotes/general/days_till_expired', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 5;

            $duplicateQuote = $this->quotesQuoteFactory->create();
            /* @var $quote Epicor_Quotes_Model_Quote */
            $duplicateQuote->addCustomerId($customer->getId());
            $duplicateQuote->setErpAccountId($erpAccount->getId());
            $duplicateQuote->setCurrencyCode($this->storeManager->getStore()->getBaseCurrencyCode());

            $customerGlobal = $this->scopeConfig->isSetFlag('epicor_quotes/general/allow_customer_global', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($customer->isCustomer() && $customerGlobal) {
                $isGlobal = $this->request->getParam('is_global') == 1 ? true : false;
                $duplicateQuote->setIsGlobal($isGlobal);
            }

            if ($commHelper->isMasquerading()) {
                $duplicateQuote->setIsGlobal(true);
            }

            // save first user comment only
            $firstNote = $this->quotesResourceQuoteNoteCollectionFactory->create()->addFieldToFilter('quote_id', $currentQuoteId)
                    ->addFieldToFilter('admin_id', array(array('null' => true), 0))
                    ->setOrder('created_at', 'ASC')->getFirstItem();
            /* @var $firstNote Epicor_Quotes_Model_Quote_Note */

            if ($firstNote) {
                $duplicateQuote->addNote($firstNote->getNote());
            }


            $duplicateQuote->setExpires(strtotime('+' . $daysTillExpired . ' days'));
            $duplicateQuote->setStatusId(\Epicor\Quotes\Model\Quote::STATUS_PENDING_RESPONSE);

            foreach ($currentQuoteProductlines->getItems() as $product) {


                $productId = $product->getProductId();
                /* @var $product Epicor_Quotes_Model_Quote_Product */
                /* @var $quoteProduct Epicor_Quotes_Model_Quote_Product */
                $quoteProduct = $this->quotesQuoteProductFactory->create();
                $quoteProduct->setProductId($productId);
                $quoteProduct->setOrigQty($product->getOrigQty());
                $quoteProduct->setOrigPrice($product->getOrigPrice());
                $quoteProduct->setNewQty($product->getNewQty());
                $quoteProduct->setNewPrice($product->getNewPrice());
                $quoteProduct->setNote($product->getNote());
                $quoteProduct->setLocationCode($product->getLocationCode());

                $options = $product->getOptions();
                if (isset($options)) {
                    $quoteProduct->setOptions($product->getOptions());
                }

                $duplicateQuote->addItem($quoteProduct);
            }

            $duplicateQuote->setStoreId($this->storeManager->getStore()->getId());

            $duplicateQuote->save();

            $this->messageManager->addSuccess($success);
            //M1 > M2 Translation Begin (Rule p2-4)
            //$this->_redirectUrl(Mage::getUrl('epicor_quotes/manage/view/', array('id' => $duplicateQuote->getId())));
            $this->_redirect($this->_url->getUrl('epicor_quotes/manage/view/', array('id' => $duplicateQuote->getId())));
            //M1 > M2 Translation End
        } catch (\Exception $e) {
            $this->checkoutSession->addError($error . $e->getMessage());
            //M1 > M2 Translation Begin (Rule p2-4)
            //$this->_redirectUrl(Mage::getUrl('epicor_quotes/manage/view/', array('id' => $currentQuoteId)));
            $this->_redirect($this->_url->getUrl('epicor_quotes/manage/view/', array('id' => $currentQuoteId)));
            //M1 > M2 Translation End
        } catch (Mage_Exception $e) {
            $this->checkoutSession->addError($error);
            //M1 > M2 Translation Begin (Rule p2-4)
            //$this->_redirectUrl(Mage::getUrl('epicor_quotes/manage/view/', array('id' => $currentQuoteId)));
            $this->_redirect($this->_url->getUrl('epicor_quotes/manage/view/', array('id' => $currentQuoteId)));
            //M1 > M2 Translation End
        }
    }

    }
