<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller;

//use Magento\Checkout\Model\Cart as CustomerCart;

/**
 * Shopping cart controller
 */
abstract class Cart extends \Magento\Checkout\Controller\Cart
{

    protected $_products;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory
     */
    protected $wishlistResourceModelItemCollectionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    
    /*
     * @var \Magento\Checkout\Model\Session
     */
   // protected $checkoutSession;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistResourceModelItemCollectionFactory)
    {
        $this->checkoutCart = $checkoutCart;
        $this->commProductHelper = $commProductHelper;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->storeManager = $storeManager;
        //$this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->wishlistResourceModelItemCollectionFactory = $wishlistResourceModelItemCollectionFactory;

        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $checkoutCart);
    }

    /**
     * Adding multiple products to shopping cart action
     * based on Mage_Checkout_CartController::addAction()
     * see also http://www.magentocommerce.com/boards/viewthread/8610/
     * and http://www.magentocommerce.com/wiki/how_to_overload_a_controller
     */
    public function _addmultiple($productIds, $configure, $params = array())
    {
        $cart = $this->checkoutCart;
        /* @var $cart \Epicor\Comm\Model\Cart */

        $helper = $this->commProductHelper;
        /* @var $helper \Epicor\Comm\Helper\Product */

        $locHelper = $this->commLocationsHelper;
        /* @var $locHelper Epicor_Comm_Helper_Locations */

        $locEnabled = $locHelper->isLocationsEnabled();

        $configureProducts = array();
        foreach ($productIds as $productId => $request) {
            $product = $this->catalogProductFactory->create()
                ->setStoreId($this->storeManager->getStore()->getId())
                ->load($productId);

            if ($configure && ($product->getTypeId() == 'configurable' || $helper->productHasCustomOptions($product, true))) {
                $configureProducts[] = $productId;
            } else {
                if (isset($request['multiple'])) {
                    foreach ($request['multiple'] as $key => $mRequest) {
                        if ($locEnabled) {
                            $locationCode = $mRequest['location_code'];
                            $newQty = $helper->getCorrectOrderQty($product, $mRequest['qty'], $locEnabled, $locationCode);
                            //Minimum and Maximum Qty check for product
                            if ($newQty['qty'] != $mRequest['qty']) {
                                $mRequest['qty'] = $newQty['qty'];
                                $params['products'][$productId]['multiple'][$key]['qty'] = $newQty['qty'];
                                $message = $newQty['message'];
                                $this->checkoutSession->addSuccess($message);
                            }
                        }
                        $requestData = array_merge($params, $mRequest);

                        if (isset($mRequest['super_group_locations']) && $mRequest['super_group_locations']) {
                            $this->_addSuperGroup($requestData, $mRequest['super_group_locations'], $product, $configure, false);
                        } else {
                            if ((is_numeric($mRequest['qty']) && $mRequest['qty'] > 0) || (isset($mRequest['super_group']) && max($mRequest['super_group']) > 0)) {
                                $this->_addProduct($product, $requestData);
                            }
                        }
                    }
                } else {
                    if ($request['super_group_locations']) {
                        $this->_addSuperGroup($request, $request['super_group_locations'], $product, $configure, false);
                    } else {
                        if (is_numeric($request['qty']) && $request['qty'] > 0) {
                            $this->_addProduct($product, $request);
                        }
                    }
                }
            }

            if ($locEnabled) {
                $customer = $this->customerSession->getCustomer();
                $customerId = $customer->getId();
                $wishlistProduct = $this->wishlistResourceModelItemCollectionFactory->create()// remove from wishlist if locations is enabled 
                    ->addFieldToFilter('product_id', array('eq' => $product->getId()))
                    ->addCustomerIdFilter($customer->getId())
                    ->addStoreData()
                    ->getFirstItem();
                if (!$wishlistProduct->isObjectNew()) {
                    $wishlistProduct->delete();
                }
            }
            //M1 > M2 Translation Begin (Rule 55)
            //$message = $this->__('%s was successfully added to your shopping cart.', $product->getName());
            $cart->save();
           if (!$cart->getQuote()->getHasError()) {
               $message = __('%1 was successfully added to your shopping cart.', $product->getName());
               //M1 > M2 Translation End
                $this->checkoutSession->addSuccess($message);
           }
        }

        if ($configure && !empty($configureProducts)) {
            $helper->addConfigureListProducts($configureProducts);
        }

        $cart->save();
    }

    protected function _addProduct($product, $request)
    {
        $cart = $this->checkoutCart;
        /* @var $cart \Epicor\Comm\Model\Cart */

        $eventArgs = array(
            'product' => $product,
            'qty' => isset($request['qty']) ? ($request['qty'] ?: 1) : 1,
            'request' => $request,
            'response' => $this->getResponse(),
            'super_group' => isset($request['super_group']) ? $request['super_group'] : null,
            'bundle_option' => isset($request['bundle_option']) ? $request['bundle_option'] : null,
            'location_code' => isset($request['location_code']) ? $request['location_code'] : ''
        );

        $this->_eventManager->dispatch('checkout_cart_before_add', $eventArgs);
        $requestInfo = array_merge($request, $eventArgs);
        unset($requestInfo['product']);
        $cart->addProduct($product, $requestInfo);
        $this->_eventManager->dispatch('checkout_cart_after_add', $eventArgs);
    }

    public function _addSuperGroup($request, $superGroup, $product, $configure = null, $saveCart = true)
    {
        $cart = $this->checkoutCart;
        /* @var $cart \Epicor\Comm\Model\Cart */

        $helper = $this->commProductHelper;
        /* @var $helper Epicor_Comm_Helper_Product */

        $locHelper = $this->commLocationsHelper;
        /* @var $locHelper Epicor_Comm_Helper_Locations */

        $locEnabled = $locHelper->isLocationsEnabled();
        if (isset($request['update_config_value'])) {
            unset($request['update_config_value']);
        }
        foreach ($superGroup as $locationCode => $group) {
            $processedGroup = array();

            foreach ($group as $productId => $qty) {
                if (is_numeric($qty) && !empty($qty)) {
                    if ($locEnabled) {
                        $prod = $this->catalogProductFactory->create()->load($productId);
                        $newQty = $helper->getCorrectOrderQty($prod, $qty, $locEnabled, $locationCode);
                        //Minimum and Maximum Qty check for product
                        if ($newQty['qty'] != $qty) {
                            $qty = $newQty['qty'];
                            $message = $newQty['message'];
                            $this->checkoutSession->addSuccess($message);
                        }
                    }
                    $processedGroup[$productId] = $qty;
                }
            }
            if (array_sum($processedGroup) > 0) {
                $eventArgs = array(
                    'product' => $product,
                    'qty' => (isset($request['qty']) && $request['qty']) ?: 1,
                    'request' => $request,
                    'response' => $this->getResponse(),
                    'super_group' => $processedGroup,
                    'bundle_option' => isset($request['bundle_option']) ? $request['bundle_option'] : null,
                    'location_code' => $locationCode
                );

                $this->_eventManager->dispatch('checkout_cart_before_add', $eventArgs);
                $cart->addProduct($product, $eventArgs);
                $this->_eventManager->dispatch('checkout_cart_after_add', $eventArgs);
            }
        }

        if ($saveCart) {

            $cart->save();
            //M1 > M2 Translation Begin (Rule 55)
            //$message = $this->__('%s was successfully added to your shopping cart.', $product->getName());
           if (!$cart->getQuote()->getHasError()) {
               $message = __('%1 was successfully added to your shopping cart.', $product->getName());
                //M1 > M2 Translation End
                $this->checkoutSession->addSuccess($message);
           }
        }
    }
/**
     * Empty customer's shopping cart
     */
    protected function _emptyShoppingCart()
    {
        $this->_eventManager->dispatch('checkout_cart_empty', array());
        parent::_emptyShoppingCart();
    }
/**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _goBack($backUrl = null)
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl) {
            if (!$this->_isInternalUrl($returnUrl)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('External urls redirect to "' . $returnUrl . '" denied!'));
            }

            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!$this->_scopeConfig->getValue('checkout/cart/redirect_to_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && !$this->getRequest()->getParam('in_cart') && !$this->getRequest()->getParam('update_config_value') && $backUrl = $this->_redirect->getRefererUrl()
        ) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return $this;
    }
}
