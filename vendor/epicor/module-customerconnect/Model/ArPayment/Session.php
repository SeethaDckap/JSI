<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment;

use Magento\Customer\Api\Data\CustomerInterface;
use Epicor\Customerconnect\Model\ArPayment\Quote;

/**
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Session extends \Magento\Framework\Session\SessionManager
{
    /**
     * Checkout state begin
     */
    const CHECKOUT_STATE_BEGIN = 'begin';
    const IGNORE_ACTION_NAMES_FOR_UPDATE = [
        'customerconnect_arpayments_archeckout',
        '__',
        'customerconnect_arpayments_updateinvoices',
        'customerconnect_arpayments_checkout',
    ];

    /**
     * Quote instance
     *
     * @var Quote
     */
    protected $_quote;

    /**
     * Customer Data Object
     *
     * @var CustomerInterface|null
     */
    protected $_customer;

    /**
     * Whether load only active quote
     *
     * @var bool
     */
    protected $_loadInactive = false;

    /**
     * Loaded order instance
     *
     * @var \Epicor\Customerconnect\Model\Order
     */
    protected $_order;

    /**
     * @var \Epicor\Customerconnect\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;


    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;
    
    
    protected $arPaymentsModel;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Epicor\Customerconnect\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Epicor\Customerconnect\Model\ArPayment\OrderFactory $orderFactory,
        \Epicor\Customerconnect\Model\Arpayments $arPaymentsModel,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Epicor\Customerconnect\Model\ArPayment\QuoteFactory $quoteFactory
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_customerSession = $customerSession;
        $this->_remoteAddress = $remoteAddress;
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->quoteFactory = $quoteFactory;
        $this->arPaymentsModel=$arPaymentsModel;
        
        
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );
    }

    /**
     * Set customer data.
     *
     * @param CustomerInterface|null $customer
     * @return \Magento\Checkout\Model\Session
     * @codeCoverageIgnore
     */
    public function setCustomerData($customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * Check whether current session has quote
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function hasQuote()
    {
        return isset($this->_quote);
    }

    /**
     * Set quote to be loaded even if inactive
     *
     * @param bool $load
     * @return $this
     * @codeCoverageIgnore
     */
    public function setLoadInactive($load = true)
    {
        $this->_loadInactive = $load;
        return $this;
    }

    /**
     * Get checkout quote instance by current session
     *
     * @return Quote
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getQuote()
    {
        if ($this->_quote === null) {
            $quote = $this->quoteFactory->create();
            if ($this->getQuoteId()) {
                try {
                   $quote = $this->quoteFactory->load($this->getQuoteId());
                    
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->setQuoteId(null);
                }
            }
            if (!$this->getQuoteId() && ($this->_customerSession->isLoggedIn())) {
                    $customer = $this->_customerSession->getCustomer();
                    $quote = $quote->loadByCustomer($customer);
                    $quote->setCustomer($this->customerRepository->getById($customer->getId()));
                    $quote->setStore($customer->getStore());
                    if (!in_array($this->request->getFullActionName(), self::IGNORE_ACTION_NAMES_FOR_UPDATE)) {
                        $this->arPaymentsModel->getErpAccountAddress($quote);
                    }
            }
            $this->_quote = $quote;
        }

        $remoteAddress = $this->_remoteAddress->getRemoteAddress();
        if ($remoteAddress) {
            $this->_quote->setRemoteIp($remoteAddress);
        }


        return $this->_quote;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    protected function _getQuoteIdKey()
    {
        return 'quote_id_' . $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * @param int $quoteId
     * @return void
     * @codeCoverageIgnore
     */
    public function setQuoteId($quoteId)
    {
        $this->storage->setData($this->_getQuoteIdKey(), $quoteId);
    }

    /**
     * @return int
     * @codeCoverageIgnore
     */
    public function getQuoteId()
    {
        return $this->getData($this->_getQuoteIdKey());
    }

  

    /**
     * Destroy/end a session
     * Unset all data associated with object
     *
     * @return $this
     */
    public function clearQuote()
    {
        $this->_eventManager->dispatch('ar_checkout_quote_destroy', ['quote' => $this->getQuote()]);
        $this->_quote = null;
        $this->setQuoteId(null);
        $this->setLastSuccessQuoteId(null);
        return $this;
    }

    /**
     * Unset all session data and quote
     *
     * @return $this
     */
    public function clearStorage()
    {
        parent::clearStorage();
        $this->_quote = null;
        return $this;
    }

    /**
     * Clear misc checkout parameters
     *
     * @return void
     */
    public function clearHelperData()
    {
        $this->setRedirectUrl(null)->setLastOrderId(null)->setLastRealOrderId(null)->setAdditionalMessages(null);
    }

    /**
     * @return $this
     * @codeCoverageIgnore
     */
    public function resetCheckout()
    {
        $this->setCheckoutState(self::CHECKOUT_STATE_BEGIN);
        return $this;
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function replaceQuote($quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Get order instance based on last order ID
     *
     * @return \Epicor\Customerconnect\Model\Order
     */
    public function getLastRealOrder()
    {
        $orderId = $this->getLastRealOrderId();
        if ($this->_order !== null && $orderId == $this->_order->getIncrementId()) {
            return $this->_order;
        }
        $this->_order = $this->_orderFactory->create();
        if ($orderId) {
            $this->_order->loadByIncrementId($orderId);
        }
        return $this->_order;
    }

   
}
