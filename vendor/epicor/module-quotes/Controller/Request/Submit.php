<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Request;

class Submit extends \Epicor\Quotes\Controller\Request
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    //protected $cache;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Epicor\Quotes\Model\Quote\ProductFactory
     */
    protected $quotesQuoteProductFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    //protected $generic;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
     public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        //\Magento\Framework\App\CacheInterface $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Quotes\Model\Quote\ProductFactory $quotesQuoteProductFactory, 
        \Magento\Checkout\Model\Cart $checkoutCart
        )
    {
        $this->scopeConfig = $scopeConfig;
       // $this->cache = $cache;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->commHelper = $commHelper;
        $this->request = $request;
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->checkoutSession = $checkoutSession;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->quotesQuoteProductFactory = $quotesQuoteProductFactory;
        $this->checkoutCart = $checkoutCart;
        //$this->generic = $generic;
        $this->messageManager = $context->getMessageManager();
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

        $success = __('Quote was submitted for review.');
        $error = __('Quote failed to be submitted for review.');

        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */
        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
      
        $eccSelectedContract = $contractHelper->getSelectedContractCode();

        if (!$customerSession->authenticate()) {
            return;
        }

        try {
            $commHelper = $this->commHelper;
            /* @var $commHelper Epicor_Comm_Helper_Data */
            $erpAccount = $commHelper->getErpAccountInfo();
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
            $daysTillExpired = $this->scopeConfig->getValue('epicor_quotes/general/days_till_expired', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 5;

            $comments = $this->request->getParam('comment');
            $quote = $this->quotesQuoteFactory->create();
            /* @var $quote Epicor_Quotes_Model_Quote */
            $quote->addCustomerId($customer->getId());
            $quote->setErpAccountId($erpAccount->getId());
            $quote->setCurrencyCode($this->storeManager->getStore()->getBaseCurrencyCode());

            $customerGlobal = $this->scopeConfig->isSetFlag('epicor_quotes/general/allow_customer_global', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($customer->isCustomer() && $customerGlobal) {
                $isGlobal = $this->request->getParam('is_global') == 1 ? true : false;
                $quote->setIsGlobal($isGlobal);
            }

            if ($commHelper->isMasquerading()) {
                $quote->setIsGlobal(true);
            }

            if (!empty($comments['quote'])) {
                $quote->addNote($comments['quote']);
            }

            $quote->setExpires(strtotime('+' . $daysTillExpired . ' days'));
            $quote->setStatusId(\Epicor\Quotes\Model\Quote::STATUS_PENDING_RESPONSE);
            $quote->setContractCode($eccSelectedContract);

            $sessionQuote = $this->checkoutSession->getQuote();
            /* @var $sessionQuote Mage_Sales_Model_Quote */

            $productModel = $this->catalogProductFactory->create();
            /* @var $productModel Epicor_Comm_Model_Product */

            foreach ($sessionQuote->getAllItems() as $product) {
                if ($product->getParentItemId() != null) {
                    continue;
                }
                $note = isset($comments[$product->getId()]) ? $comments[$product->getId()] : null;
                $productId = $productModel->getIdBySku($product->getSku());
                /* @var $product Mage_Sales_Model_Quote_Item */
                $quoteProduct = $this->quotesQuoteProductFactory->create();
                $quoteProduct->setProductId($productId);
                $quoteProduct->setOrigQty($product->getQty());
                $quoteProduct->setOrigPrice($product->getPrice());
                $quoteProduct->setNewQty($product->getQty());
                $quoteProduct->setNewPrice($product->getPrice());
                $quoteProduct->setNote($note);
                $quoteProduct->setLocationCode($product->getEccLocationCode());
                $quoteProduct->setContractCode($product->getEccContractCode());

                $helper = $this->commHelper;
                /* @var $helper Epicor_Comm_Helper_Data */

                $options = $helper->getItemProductOptions($product);
                switch (true) {
                    case isset($options['options']):
                        $itemOptions = $options['options'];
                        break;
                    case isset($options['attributes_info']):
                        $itemOptions = $options['attributes_info'];
                        break;
                    default:
                        $itemOptions = [];
                        break;
                }
                if (empty($itemOptions) === false) {
                    $quoteProduct->setOptions(serialize($itemOptions));
                }
                $quote->addItem($quoteProduct);
            }

            $quote->setStoreId($this->storeManager->getStore()->getId());
            $quote->save();

            //$basket = $this->checkoutCart;
            //$basket->truncate()->save();
            try {
                $this->checkoutCart->truncate()->save();
            } catch (\Magento\Framework\Exception\LocalizedException $exception) {
                $this->messageManager->addError($exception->getMessage());
            } catch (\Exception $exception) {
                $this->messageManager->addException($exception, __('We can\'t update the shopping cart.'));
            }
            
            $this->checkoutSession->setCartWasUpdated(true);
            //$this->generic->addSuccess($success);
            $this->messageManager->addSuccess($success);
            //M1 > M2 Translation Begin (Rule p2-4)
            //$this->_redirectUrl(Mage::getUrl('epicor_quotes/manage/view/', array('id' => $quote->getId())));
            $this->_redirect($this->_url->getUrl('epicor_quotes/manage/view/', array('id' => $quote->getId())));
            //M1 > M2 Translation End
        } catch (\Exception $e) {
            $this->checkoutSession->addError($error . $e->getMessage());
            //M1 > M2 Translation Begin (Rule p2-4)
            //$this->_redirectUrl(Mage::getUrl('checkout/cart'));
            $this->_redirect($this->_url->getUrl('checkout/cart'));
            //M1 > M2 Translation End
        } catch (Mage_Exception $e) {
            $this->checkoutSession->addError($error);
            //M1 > M2 Translation Begin (Rule p2-4)
            //$this->_redirectUrl(Mage::getUrl('checkout/cart'));
            $this->_redirect($this->_url->getUrl('checkout/cart'));
            //M1 > M2 Translation End
        }
    }

}
