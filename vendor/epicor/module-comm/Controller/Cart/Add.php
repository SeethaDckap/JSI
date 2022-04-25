<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\LocalizedException;

class Add extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $productHelper;

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

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableProductProductTypeConfigurable;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutCartHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModel;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $locationsHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory
     */
    protected  $collectionFactory;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 100.1.0
     */
    protected $productMetadata;

    /**
     * @var array
     */
    protected $addProductIds = [];

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProductProductTypeConfigurable,
        \Magento\Checkout\Helper\Cart $checkoutCartHelper,
        \Epicor\Comm\Helper\Product $productHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\ProductFactory $productModel,
        \Epicor\Comm\Helper\Locations $locationsHelper,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $collectionFactory,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    )
    {
        $this->registry = $registry;
        $this->logger = $logger;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->branchPickupHelper = $branchPickupHelper;
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->configurableProductProductTypeConfigurable = $configurableProductProductTypeConfigurable;
        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->productHelper = $productHelper;
        $this->jsonHelper = $jsonHelper;
        $this->productModel = $productModel;
        $this->locationsHelper = $locationsHelper;
        $this->escaper = $escaper;
        $this->localeResolver = $localeResolver;
        $this->collectionFactory = $collectionFactory;
        $this->productMetadata = $productMetadata;
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart, $productRepository);
    }


    public function execute()
    {
        $cart = $this->cart;

        $params = $this->getRequest()->getParams();
        $productIds = $this->getRequest()->getParam('products');
        $superGroup = $this->getRequest()->getParam('super_group_locations');
        $configure = $this->getRequest()->getParam('configurelist');

        if (!empty($productIds) || !empty($superGroup)) {
            if ($this->registry->registry('Epicor_No_Valid_Qty_Selected')) {
                // set in observer checkQtySelected. Will be false if locations is off or a valid qty selected for the product
                $this->messageManager->addError(__('Please select a valid quantity for this product'));
            } else {
                try {
                    if (!empty($productIds)) {
                        $this->_addmultiple($productIds, $configure, $params);
                    } else {
                        $product = $this->_initProduct();
                        $this->_addSuperGroup($params, $superGroup, $product);
                    }
                } catch (LocalizedException $e) {
                    if ($this->_checkoutSession->getUseNotice(true)) {
                        $this->messageManager->addNoticeMessage($e->getMessage());
                    } else {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                    $this->logger->critical($e);
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Can not add item to shopping cart'));
                }
            }
            $this->_goBack();
        } else {
            if (isset($params['update_config_value'])) {
                $cartItemId = $this->getRequest()->getParam('cart_item_id');
                $superGroup = $this->getRequest()->getParam('super_group');
                $superGroupQty = reset($superGroup);            // return first value in super group array
                $cart = array($cartItemId => array('qty' => $superGroupQty));
                $this->getRequest()->setParam('cart', $cart);
                $this->_updateShoppingCart();
                $this->_goBack();
            } else {
                $locHelper = $this->commLocationsHelper;
                $branchHelper = $this->branchPickupHelper;
                $locEnabled = $locHelper->isLocationsEnabled();
                if ($locEnabled) {
                    $locationCode = $locHelper->getDefaultLocationCode();
                    if (isset($params['location_code'])) {
                        $locationCode = $params['location_code'];
                    } else if ($branchHelper->isBranchPickupAvailable() && $branchHelper->getSelectedBranch()) {
                        $locationCode = $branchHelper->getSelectedBranch();
                    }

                    $helper = $this->commProductHelper;
                    if (isset($params['super_group'])) {
                        $products = array_keys($params['super_group']);
                        foreach ($products as $prod) {
                            $product = $this->catalogProductFactory->create()->load($prod);
                            $newQty = $helper->getCorrectOrderQty($product, $params['super_group'][$prod], $locEnabled, $locationCode);
                            //Minimum and Maximum Qty check for product
                            if ($newQty['qty'] != $params['super_group'][$prod]) {
                                $params['super_group'][$prod] = $newQty['qty'];
                                $message = $newQty['message'];
                                $this->messageManager->addSuccess($message);
                            }
                        }
                    } else if (isset($params['super_attribute'])) {
                        $configurableProduct = $this->catalogProductFactory->create()->load($params['product']);
                        $product = $this->configurableProductProductTypeConfigurable->getProductByAttributes($params['super_attribute'], $configurableProduct);
                        $quantity=isset($params['qty'])?$params['qty']:1;
                        $params['qty'] = $quantity;
                        $newQty = $helper->getCorrectOrderQty($product, $params['qty'], $locEnabled, $locationCode);
                    } else {
                        $product = $this->catalogProductFactory->create()->load($params['product']);
                        if (!isset($params['qty']) || ($params['qty'] == 0)) {
                            $params['qty'] = 1;
                        }
                        $newQty = $helper->getCorrectOrderQty($product, $params['qty'], $locEnabled, $locationCode);
                    }

                    if (!isset($params['super_group'])) {
                        //Minimum and Maximum Qty check for product
                        if ($newQty['qty'] != $params['qty']) {
                            $params['qty'] = $newQty['qty'];
                            $message = $newQty['message'];
                            $this->messageManager->addSuccessMessage($message);
                        }
                    }

                    if (isset($params['qty']) && $params['qty'] == 0) {
                        $url = $this->_getRefererUrl();
                        $checkoutHelper = $this->checkoutCartHelper;
                        if (strpos($url, 'quickorderpad') || !$checkoutHelper->getShouldRedirectToCart()) {
                            $this->_redirectReferer();
                        } else {
                            $this->_redirectUrl($checkoutHelper->getCartUrl());
                        }
                        return;
                    }
                    $this->getRequest()->setParams($params);
                }
                $this->setPostQtyInParam();
                parent::execute();
            }
        }
    }

    /**
     * resolves a conflict in parameters, where qty is in the url
     * and qty is in the post request are in conflict, set params
     * with qty in post request
     */
    private function setPostQtyInParam()
    {
        $params = $this->getRequest()->getParams();
        $postData = $this->getRequest()->getPostValue();

        $postQty = $postData['qty'] ?? '';
        $paramQty = $params['qty'] ?? '';
        if (($postQty && $paramQty) && ($postQty !== $paramQty)) {
            $params['qty'] = $postQty;
        }
        $this->getRequest()->setParams($params);
    }

    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return $this->_goBack($backUrl);
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }

        $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($result)
        );
    }

    protected function _goBack($backUrl=null)
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl) {

            if (!$this->_isInternalUrl($returnUrl)) {
                throw new \Exception('External urls redirect to "' . $returnUrl . '" denied!');
            }

            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!$this->_scopeConfig->getValue('checkout/cart/redirect_to_cart',\Magento\Store\Model\ScopeInterface::SCOPE_STORE) && !$this->getRequest()->getParam('in_cart') && !$this->getRequest()->getParam('update_config_value') && $backUrl = $this->_redirect->getRefererUrl()
        ) {
            if ($this->getRequest()->isAjax()) { // is Ajax request
                if(!empty($backUrl)){
                    $url_parts = explode('/', $backUrl);
                    if(isset($url_parts[3]) && $url_parts[3] =='wishlist'){
                        $backUrl = $this->_url->getUrl('wishlist/index');
                    }
                }

                $result['backUrl'] = $backUrl;
                $result = [];
                $this->getResponse()->representJson(
                        $this->jsonHelper->jsonEncode($result)
                );
            } else {
                if(!empty($backUrl)){
                    $url_parts = explode('/', $backUrl);
                    if(isset($url_parts[3]) && $url_parts[3] =='wishlist'){
                        $backUrl = $this->_url->getUrl('wishlist/index');
                    }
                }
                $this->getResponse()->setRedirect($backUrl);
            }
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_checkoutSession->setContinueShoppingUrl($this->_redirect->getRefererUrl());
            }

            if ($this->getRequest()->isAjax()) { // is Ajax request
                $result['backUrl'] = $this->_url->getUrl('checkout/cart');
                $this->getResponse()->representJson(
                        $this->jsonHelper->jsonEncode($result)
                );
            } else {
                $this->_redirect('checkout/cart');
            }
        }
        return $this;
    }


    public function _addmultiple($productIds, $configure, $params = array())
    {
        $cart = $this->cart;
        /* @var $cart \Epicor\Comm\Model\Cart */

        $helper = $this->productHelper;
        /* @var $helper \Epicor\Comm\Helper\Product */

        $commHelper = $this->commProductHelper;
        /* @var $commHelper \Epicor\Comm\Helper\Product */

        $locHelper = $this->commLocationsHelper;
        /* @var \Epicor\BranchPickup\Helper\Data */

        $locEnabled = $locHelper->isLocationsEnabled();
        $configureProducts = array();
        foreach ($productIds as $productId => $request) {
            $this->addProductIds[$productId] = $request;
            $product = $this->productModel->create()
                ->setStoreId($this->_storeManager->getStore()->getId())
                ->load($productId);

            if($product->getTypeId() == 'configurable'){
                $configSimpleProductId = $this->getRequest()->getParam('selected_configurable_option');
                $this->addProductIds[$configSimpleProductId] = 'configurable product';
            }
            if ($configure && ($product->getTypeId() == 'configurable' || $helper->productHasCustomOptions($product, true))) {
                $configureProducts[] = $productId;
            } else {
                if (isset($request['multiple'])) {
                    foreach ($request['multiple'] as $key => $mRequest) {
                        if ($locEnabled && isset($mRequest['location_code'])) {
                            $locationCode = $mRequest['location_code'];
                            $newQty = $commHelper->getCorrectOrderQty($product, $mRequest['qty'], $locEnabled, $locationCode);
                            //Minimum and Maximum Qty check for product
                            if ($newQty['qty'] != $mRequest['qty']) {
                                $mRequest['qty'] = $newQty['qty'];
                                $params['products'][$productId]['multiple'][$key]['qty']  = $newQty['qty'];
                                $message = $newQty['message'];
                                $this->messageManager->addSuccessMessage($message);
                            }
                        }
                        $requestData = array_merge($params, $mRequest);

                        if (isset($mRequest['super_group_locations']) && $mRequest['super_group_locations']) {
                            $this->_addSuperGroup($requestData, $mRequest['super_group_locations'], $product, $configure, false);
                        } else {
                            if ((isset($mRequest['qty']) && is_numeric($mRequest['qty']) && $mRequest['qty'] > 0)
                                || (isset($mRequest['super_group']) && max($mRequest['super_group']) > 0)) {
                                if (isset($mRequest['super_group']) && count($mRequest['super_group']) > 0) {
                                    foreach ($mRequest['super_group'] as $key => $value) {
                                        $this->addProductIds[$key] = "Super Group";
                                        break;
                                    }
                                }
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

            if($this->locationsHelper->isLocationsEnabled()){
                $customer = $this->cart->getCustomerSession()->getCustomer();
//                $customerId = $customer->getId();
                $wishlistProduct = $this->collectionFactory->create()                           // remove from wishlist if locations is enabled
                ->addFieldToFilter('product_id', array('eq'=>$product->getId()))
                    ->addCustomerIdFilter($customer->getId())
                    ->addStoreData()
                    ->getFirstItem();
                if(!$wishlistProduct->isObjectNew()){
                    $wishlistProduct->delete();
                }
            }

//            if (!$cart->getQuote()->getHasError()) {
//                $message = __('%1 was successfully added to your shopping cart.', $product->getName());
//                $this->messageManager->addSuccessMessage($message);
//            }
        }
        if ($this->registry->registry('add_multiple_to_cart')) {
            $this->registry->unregister('add_multiple_to_cart');
        }
        $this->registry->register('add_multiple_to_cart', $this->addProductIds);

        $cart->save();

        if ($configure && !empty($configureProducts)) {
            $helper->addConfigureListProducts($configureProducts);
        }
    }

    protected function _addProduct($product, $request)
    {
        $cart = $this->cart;
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
        $cart = $this->cart;
        /* @var $cart \Epicor\Comm\Model\Cart */

        $commHelper = $this->commProductHelper;
        /* @var $commHelper \Epicor\Comm\Helper\Product */

        $locHelper = $this->commLocationsHelper;
        /* @var \Epicor\BranchPickup\Helper\Data */

        $locEnabled = $locHelper->isLocationsEnabled();
        foreach ($superGroup as $locationCode => $group) {

            $processedGroup = array();

            foreach ($group as $productId => $qty) {
                    $this->addProductIds[$productId] = "associated parent";
                    $prod = $this->catalogProductFactory->create()->load($productId);
                    if ($locEnabled) {
                        $newQty = $commHelper->getCorrectOrderQty($prod, $qty, $locEnabled, $locationCode);
                        //Minimum and Maximum Qty check for product
                        if ($newQty['qty'] != $qty) {
                            $qty = $newQty['qty'];
                            $message = $newQty['message'];
                            $this->messageManager->addSuccessMessage($message);
                        }
                    }
                    $processedGroup[$productId] = $qty;
            }

            if (array_sum($processedGroup) > 0) {
                $eventArgs = array(
                    'product' => $product,
                    'qty' => (isset($request['qty']) && $request['qty']) ? : 1,
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
        if ($this->registry->registry('add_multiple_to_cart')) {
            $this->registry->unregister('add_multiple_to_cart');
        }
        $this->registry->register('add_multiple_to_cart', $this->addProductIds);

        if ($saveCart) {
            $cart->save();
//            if (!$cart->getQuote()->getHasError()) {
//                $message = __('%1 was successfully added to your shopping cart.', $product->getName());
//                $this->messageManager->addSuccessMessage($message);
//            }
        }
        /**
            * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
            */
           $this->_eventManager->dispatch(
               'checkout_cart_add_product_complete',
               ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
           );
    }


    protected function _updateShoppingCart()
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->localeResolver->getLocale()]
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                if (!$this->cart->getCustomerSession()->getCustomerId() && $this->cart->getQuote()->getCustomerId()) {
                    $this->cart->getQuote()->setCustomerId(null);
                }

                $cartData = $this->cart->suggestItemsQty($cartData);
                $this->cart->updateItems($cartData)->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            //M1 > M2 Translation Begin (Rule 20)
            //$this->_getSession()->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
            $this->messageManager->addError(
                $this->escaper->escapeHtml($e->getMessage())
            );
            //M1 > M2 Translation End
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the shopping cart.'));
            $this->logger->critical($e->getMessage());
        }
    }

}
