<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Sales;

use Magento\Customer\Model\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Controller\Guest\OrderLoader as GuestOrderLoader;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Orders controller
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
abstract class Order extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $salesOrderConfig;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Common\Helper\Cart
     */
    protected $commonCartHelper;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /*
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;
    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
     */
    protected $orderLoader;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;
     /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
     /**
     * @var \Magento\Framework\App\Config\StoreManagerInterface
     */
    protected $storeManager;
     /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commData;
     /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $catalogProductResourceModel;
     /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $catalogProduct;
    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonData;

    /**
     * @var GuestOrderLoader
     */
    private $guestOrderLoader;

    /**
     * @var HttpContext
     */
    private $httpContext;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $salesOrderConfig,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Common\Helper\Cart $commonCartHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Controller\AbstractController\OrderLoaderInterface $orderLoader,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,    
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Common\Helper\Data $commonData,
        \Magento\Catalog\Model\ResourceModel\Product $catalogProductResourceModel,   
        \Magento\Catalog\Model\Product $catalogProduct,
        GuestOrderLoader $guestOrderLoader = null,
        HttpContext $httpContext = null
    ) {
        $this->_localeResolver = $localeResolver;
        $this->customerSession = $customerSession;
        $this->salesOrderConfig = $salesOrderConfig;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->commonCartHelper = $commonCartHelper;
        $this->generic = $generic;
        $this->orderLoader = $orderLoader;
        $this->_coreRegistry = $registry;
        $this->urlDecoder = $urlDecoder;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->request = $request;
        $this->checkoutCart = $checkoutCart;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->branchPickupHelper = $branchPickupHelper;
        $this->commProductHelper = $commProductHelper;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->commonData = $commonData;
        $this->catalogProductResourceModel = $catalogProductResourceModel;
        $this->catalogProduct = $catalogProduct;
        parent::__construct(
            $context
        );
        $this->guestOrderLoader = $guestOrderLoader ?: ObjectManager::getInstance()->get(GuestOrderLoader::class);
        $this->httpContext = $httpContext ?: ObjectManager::getInstance()->get(HttpContext::class);
    }


    /**
     * Check order view availability
     *
     * @param   \Epicor\Comm\Model\Order $order
     * @return  bool
     */
    protected function _canViewOrder($order)
    {
        $customerId = $this->customerSession->getCustomerId();
        $availableStates = $this->salesOrderConfig->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId) && in_array($order->getState(), $availableStates, $strict = true)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Try to load valid order by order_id and register it
     *
     * @param int $orderId
     * @return bool
     */
    protected function _loadValidOrder($orderId = null)
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->orderLoader->load($this->_request);
        } else {
            return $this->guestOrderLoader->load($this->_request);
        }
    }

protected function _reorderErp($order)
    {

        $helper = $this->customerconnectHelper;
        /* @var $helper Epicor_Customerconnect_Helper_Data */
        $erp_account_number = $helper->getErpAccountNumber();

        //M1 > M2 Translation Begin (Rule p2-6.4)
        //$result = $helper->sendOrderRequest($erp_account_number, $order->getEccErpOrderNumber(), $helper->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()));
        $result = $helper->sendOrderRequest(
                $erp_account_number,
                $order->getEccErpOrderNumber(),
                $helper->getLanguageMapping($this->_localeResolver->getLocale())
        );
        //M1 > M2 Translation End

        $cartHelper = $this->commonCartHelper;
        /* @var $cartHelper Epicor_Common_Helper_Cart */

        if (empty($result['order']) || !$cartHelper->processReorder($result['order'])) {

            if (!empty($result['error'])) {
                $this->messageManager->addError($result['error']);
            }

            if (!$this->messageManager->getMessages()->getItems()) {
                $this->messageManager->addError('Failed to build cart for Re-Order request');
            }

            $this->_redirect('checkout/cart/');
            $location = $this->urlDecoder->decode($this->request->getParam('return'));
            if (empty($location)) {
                //M1 > M2 Translation Begin (Rule p2-4)
                //$location = Mage::getUrl('sales/order/history');
                $location = $this->_url->getUrl('sales/order/history');
                //M1 > M2 Translation End
            }

            $this->_redirect($location);
        } else {
            $this->_redirect('checkout/cart/');
        }
    }

    protected function _reorderLocal($order)
    {
        $cart = $this->checkoutCart;
        /* @var $cart Mage_Checkout_Model_Cart */

        $quote = $cart->getQuote();
        $items = $order->getItemsCollection();
        $this->commonCartHelper->updateExistingCart($quote);
        $locHelper = $this->commLocationsHelper;
        $locEnabled = $locHelper->isLocationsEnabled();

        foreach ($items as $item) {
            /* @var $item Mage_Sales_Model_Order_Item */
            try {
                $product = $item->getProduct();
                /* @var $helper Epicor_Common_Helper_Cart */


                $locationCode = $item->getEccLocationCode();
                $branchHelper = $this->branchPickupHelper;
                if ($branchHelper->isBranchPickupAvailable() && $branchHelper->getSelectedBranch()) {
                    $locationCode = $branchHelper->getSelectedBranch();
                }
                $options = array(
                    'qty' => $item->getQtyOrdered(),
                    'qty' => $item->getQtyOrdered(),
                    'location_code' => $locationCode
                );


                if ($locEnabled && isset($options['location_code'])) {
                    $proHelper = $this->commProductHelper;
                    $newQty = $proHelper->getCorrectOrderQty($product, $options['qty'], $locEnabled, $locationCode);
                    if ($newQty['qty'] != $options['qty']) {
                        $options['qty'] = $newQty['qty'];
                        $message = $newQty['message'];
                        $this->messageManager->addSuccessMessage($message);
                    }
                }
                $quote->addOrUpdateLine($product, $options);
            } catch (\Exception $e) {
                if ($this->checkoutSession->getUseNotice(true)) {
                    $this->messageManager->addNoticeMessage($e->getMessage());
                } else {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
                $this->_redirect('*/*/history');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('checkout/cart');
            }
        }

        $cart->save();
        $this->_redirect('checkout/cart');
    }

}
