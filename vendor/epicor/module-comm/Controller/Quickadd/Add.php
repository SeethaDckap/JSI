<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Quickadd;

class Add extends \Epicor\Comm\Controller\Quickadd
{

    /**
     * @var \Epicor\Comm\Model\Message\Request\MsqFactory
     */
    protected $commMessageRequestMsqFactory;


    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    //protected $generic;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistWishlistFactory;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    protected $listsFrontendProductHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        //\Magento\Framework\Session\Generic $generic,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\WishlistFactory $wishlistWishlistFactory,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\DataObjectFactory $dataObjectFactory)
    {
        $this->commMessageRequestMsqFactory = $commMessageRequestMsqFactory;
        $this->wishlistHelper = $wishlistHelper;
        //$this->generic = $generic;
        $this->commHelper = $commHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->wishlistWishlistFactory = $wishlistWishlistFactory;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->scopeConfig = $scopeConfig;
        $this->branchPickupHelper = $branchPickupHelper;
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->commProductHelper = $commProductHelper;
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSession = $checkoutSession;
        parent::__construct(
            $context,
            $commMessageRequestMsqFactory,
            $wishlistHelper,
           // $generic,
            $commHelper,
            $catalogProductFactory,
            $registry,
            $customerSession,
            $wishlistWishlistFactory,
            $dataObjectFactory);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();  
        $locHelper = $this->commLocationsHelper;
        $locEnabled = $locHelper->isLocationsEnabled();
        $product =  false;
        if ($data && isset($data['sku'])) {
            $productId = isset($data['product_id']) ? $data['product_id'] : '';
            $product = $this->_initProduct($data['sku'], $productId);
            //Added to corporate BOM Reorder functionaility
            if($product && $product->getTypeId() == 'grouped' && isset($data['uom'])){
                $commHelper = $this->commHelper;
                $childSku = $data['sku'].$commHelper->getUOMSeparator().$data['uom'];
                $product = $this->_initProduct($childSku, '');
            }
        }
        if ($locEnabled) {
            /* @var $helper Epicor_Comm_Helper_Locations */
            $stockVisibility = $this->scopeConfig->getValue('epicor_comm_locations/global/stockvisibility', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (!isset($data['location_code']) || in_array($stockVisibility, (array('all_source_locations', 'default')))) {                 // if default location code required         
                $defaultLocationCode = $this->commLocationsHelper->getDefaultLocationCode();
                $branchHelper = $this->branchPickupHelper;
                if ($branchHelper->isBranchPickupAvailable() && $branchHelper->getSelectedBranch()) {
                    $defaultLocationCode = $branchHelper->getSelectedBranch();
                }
                $data['location_code'] = $defaultLocationCode;
            }

            $proHelper = $this->commProductHelper;

            if (isset($data['super_group']) && !empty($data['super_group']) && $data['super_group'] != "") {
                $_product = $this->catalogProductFactory->create()->load($data['super_group']);
                $newQty = $proHelper->getCorrectOrderQty($_product, $data['qty'], $locEnabled, $data['location_code']);
            } else if ($product) {
                $newQty = $proHelper->getCorrectOrderQty($product, $data['qty'], $locEnabled, $data['location_code']);
            }else{
                $newQty =  false;
            }
            //Minimum and Maximum Qty check for product
            if ($newQty && $newQty['qty'] != $data['qty']) {
                $data['qty'] = $newQty['qty'];
                $message = $newQty['message'];
                $this->messageManager->addSuccess($message);
                if ($newQty['qty'] == 0) {
                    //$this->_redirectReferer();
                    $this->_redirect($this->_redirect->getRefererUrl());
                    return;
                }
            }
        }

        // check sku is valid for current contract
        $listsHelper = $this->listsFrontendProductHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Product */

        if ($listsHelper->listsEnabled()) { 
            $contractHelper = $this->listsFrontendContractHelper;
            /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
            if ($listsHelper->hasFilterableLists() || $contractHelper->mustFilterByContract()) {
                $productIds = explode(',', $listsHelper->getActiveListsProductIds());
                if($product){
                    $skuProduct = $this->catalogProductFactory->create()->getIdBySku($product->getSku());
                }else{
                    $skuProduct = $this->catalogProductFactory->create()->getIdBySku($data['sku']);                    
                }
                $error = false;
                if (empty($skuProduct)) {
                    //M1 > M2 Translation Begin (Rule 55)
                    //$error = $this->__('Product %s does not exist', $data['sku']);
                    $error = __('Product %1 does not exist', $data['sku']);
                    //M1 > M2 Translation End
                    $this->messageManager->addError($error);
                } else if (in_array($skuProduct, $productIds) == false) {
                    //M1 > M2 Translation Begin (Rule 55)
                    //$error = $this->__('Product %s cannot be added to cart as it is not valid', $data['sku']);
                    $error = __('Product %1 cannot be added to cart as it is not valid', $data['sku']);
                    //M1 > M2 Translation End
                    $this->messageManager->addError($error);
                }

                if ($error) {
                    //$this->_redirectReferer();
                     $this->_redirect($this->_redirect->getRefererUrl());
                    return;
                }
            }
        }

        $redirect = '';
        try {
            if ($product) {
                $location_code = isset($data['location_code'])?$data['location_code']:[];
                $this->_checkProduct($product, $data['qty'],$location_code);

                if ($product->isSalable()) {
                    $productHelper = $this->commProductHelper;
                    /* @var $productHelper Epicor_Comm_Helper_Product */

                    if ($product->getEccConfigurator() || $product->getTypeId() == 'configurable' || $productHelper->productHasCustomOptions($product)) {
                        //M1 > M2 Translation Begin (Rule 55)
                        //$error = $this->__('Product %s requires configuration before it can be added to the Cart', $product->getSku());
                        $error = __('Product %1 requires configuration before it can be added to the Cart', $product->getSku());
                        //M1 > M2 Translation End
                        $this->messageManager->addError($error);
                        $redirect = $product->getUrlModel()->getUrl($product, array('_query' => array('qty' => $data['qty'])));
                        #$redirect = $product->getProductUrl();
                    } else if ($product->getTypeId() == 'grouped' && (!isset($data['super_group']) || empty($data['super_group']))) {
                        //M1 > M2 Translation Begin (Rule 55)
                        //$error = $this->__('Product %s cannot be added to the cart, please choose a child product', $product->getSku());
                        $error = __('Product %1 cannot be added to the cart, please choose a child product', $product->getSku());
                        //M1 > M2 Translation End
                       $this->messageManager->addError($error);
                        $redirect = $product->getProductUrl();
                    } else {
                        if ($data['target'] == 'basket') {
                            //$cart = $this->checkoutCart;
                     
                            if (isset($data['super_group'])) { 
                                $data['super_group'] = array(
                                    $data['super_group'] => $data['qty']
                                );
                            } 
                            $this->checkoutCart->getQuote()->addOrUpdateLine($product, $data);
                            $this->checkoutCart->save();
                            $this->checkoutSession->setCartWasUpdated(true);
                            //M1 > M2 Translation Begin (Rule 55)
                            //$message = $this->__('%s was successfully added to your shopping cart.', $product->getName());
                            if (!$this->checkoutCart->getQuote()->getHasError()) {
                                $message = __('%1 was successfully added to your shopping cart.', $product->getName());
                                 //M1 > M2 Translation End
                                 //$this->checkoutSession->addSuccess($message);
                                 $this->messageManager->addSuccess($message); 
                            }
                        } else if ($data['target'] == 'wishlist') {
                              $this->_addToWishlist($product, $data['qty']);
                        } else {
                            $this->messageManager->addError('Could not process add request, no destination chosen'); 
                            //$this->generic->addError('Could not process add request, no destination chosen');
                        }
                    }
                } else {
                    $this->messageManager->addError('Product not currently available'); 
                    //$this->generic->addError('Product not currently available');
                }
            } else {
                $this->messageManager->addError('Product SKU does not exist'); 
                //$this->generic->addError('Product SKU does not exist');
            }
         } catch (\Exception $e) {
            // store the error in the session here
            if (!$this->registry->registry('quote_session_error_set')) {
                $this->messageManager->addError($e->getMessage()); 
                
            }
        }
        
        if (empty($redirect)) {
            //$this->_redirectReferer();
           $this->_redirect($this->_redirect->getRefererUrl());
        } else {
            $this->getResponse()->setRedirect($redirect);
        }
    }

}
