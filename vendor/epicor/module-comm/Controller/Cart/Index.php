<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Cart;

class Index extends \Magento\Checkout\Controller\Cart\Index
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;



    /**
     * @var Magento\Framework\Escaper
     */
    protected $escaper;
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Escaper $escaper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->escaper = $escaper;
        $this->commHelper = $commHelper;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart, $resultPageFactory);
    }

    /**
     * Shopping cart display action
     */
    public function execute()
    {
        $cart = $this->cart;
        if ($cart->getQuote()->getItemsCount()) {
            //M1 > M2 Translation Begin (Rule 19)
            //$cart->init();
            //M1 > M2 Translation End
          //  $cart->save();

            if (!$cart->getQuote()->validateMinimumAmount()) {

                $customerData = $cart->getQuote()->getCustomer();
                $erpAccountIdAtt = $customerData->getCustomAttribute('ecc_erpaccount_id');
                $erpAccountId = ($erpAccountIdAtt) ? $erpAccountIdAtt->getValue() : null;
                $amount = $this->commHelper->getMinimumOrderAmount($erpAccountId);
                $_fromCurr = $cart->getQuote()->getBaseCurrencyCode();
                $_toCurr = $this->_storeManager->getStore()->getCurrentCurrencyCode();
                $minimumAmount = $this->commHelper->getCurrencyConvertedAmount($amount, $_fromCurr, $_toCurr);
//
                //M1 > M2 Translation Begin (Rule 55)
                //$warning = $this->scopeConfig->getValue('sales/minimum_order/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('sales/minimum_order/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : __('Minimum order amount is %s', $minimumAmount);
                $warning = $this->_scopeConfig->getValue('sales/minimum_order/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('sales/minimum_order/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : __('Minimum order amount is %1', $minimumAmount);
                //M1 > M2 Translation End

                //$cart->getCheckoutSession()->addNotice($warning);
            }
        }
        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                //M1 > M2 Translation Begin (Rule 20)
                //$message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                //M1 > M2 Translation End
                $messages[] = $message->getText();
            }
        }
        $this->messageManager->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_checkoutSession->setCartWasUpdated(true);

        //\Magento\Framework\Profiler::start(__METHOD__ . 'cart_display');

        //M1 > M2 Translation Begin (Rule 13)
        //$this
        //    ->loadLayout()
        //    ->_initLayoutMessages('checkout/session')
        //    ->_initLayoutMessages('catalog/session')
        //    ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        //$this->renderLayout();
        //M1 > M2 Translation End
        $resultPage = $this->resultPageFactory->create();

        $resultPage->getConfig()->getTitle()->set(__('Shopping Cart'));
        //\Magento\Framework\Profiler::stop(__METHOD__ . 'cart_display');

        return $resultPage;
    }

    }
