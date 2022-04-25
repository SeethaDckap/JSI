<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Lists;

class AddListToCart extends \Epicor\Lists\Controller\Lists
{

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutCartHelper;
    /**
     * @var Epicor\Quotes\Model\Quote
     */
    private $quote;
    /**
     * @var \Epicor\Lists\Model\ListModel\Product
     */
    private $listProduct;
    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory
     */
    protected $listProductCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    private $listName;
    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;
    /*
     * @var \Epicor\Comm\Helper\Data
     */
    protected $scopeConfig;
    /**
     * @var \Epicor\Lists\Helper\Messaging\Customer
     */
    private $listsMessagingCustomerHelper;
    /**
     * @var \Epicor\QuickOrderPad\Model\ResourceModel\Position\PositionOrder
     */
    private $positionOrder;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Checkout\Helper\Cart $checkoutCartHelper,
        \Epicor\Quotes\Model\Quote $quote,
        \Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory $listProductCollectionFactory,
        \Epicor\Lists\Model\ListModel\Product $listProduct,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Epicor\BranchPickup\Helper\Data $branchPickup,
        \Epicor\Lists\Helper\Messaging\Customer $listsMessagingCustomerHelper,
        \Epicor\QuickOrderPad\Model\ResourceModel\Position\PositionOrder $positionOrder = null
    )
    {

        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->quote = $quote;
        $this->listProductCollectionFactory = $listProductCollectionFactory;
        $this->listProduct = $listProduct;
        $this->productFactory = $productFactory;
        $this->cart = $cart;
        $this->message = $context->getMessageManager();
        $this->commLocationsHelper = $commHelper->getCommLocationsHelper();
        $this->branchPickupHelper = $branchPickup;
        $this->scopeConfig = $commHelper->getScopeConfig();
        $this->listsMessagingCustomerHelper = $listsMessagingCustomerHelper;


        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $backendJsHelper,
            $commHelper,
            $listsListModelFactory,
            $generic,
            $listsHelper,
            $listsFrontendRestrictedHelper,
            $timezone
        );
        $this->positionOrder = $positionOrder;
    }

    /**
     * Check if items in list are in the cart, if so update them, if not add them
     * return boolean
     */
    public function execute()
    {
        //addItem or Update Item
        $listId = base64_decode($this->getRequest()->getParam('id'));
        $this->addToCart($listId);
        $this->cart->save();
        $this->_redirect('*/*/');
    }

    public function addToCart($listId)
    {
        try {
            //addItem or Update Item
            $collection = $this->listProductCollectionFactory->create();
            $collection->addFieldToFilter('list_id', $listId);
            $this->setPositionOrder($collection);

            $this->listName = $this->getListModelFactory()->create()->load($listId)->getErpCode();
            $listProductSkus = [];
            $filteredListProductSkus = [];
            if (count($collection) > 0) {
                foreach ($collection as $listProduct) {
                    $listProductSkus[$listProduct->getSku() . '_' . $listProduct->getLocationCode()] = $listProduct;
                }
                //remove group product parent from array
                $list = $this->listsListModelFactory->create();
                $savedGroupProducts = $list->saveGroupedProducts($listProductSkus, [], true, true) ?: array();
                //$filteredListProductSkus = array_diff_key($listProductSkus, array_flip($savedGroupProducts));
                $filteredListProductSkus = array_filter($listProductSkus, function ($arrayValue) use ($savedGroupProducts) {
                    return !in_array($arrayValue->getSku(), $savedGroupProducts);
                });
            }

            $items = $this->cart->getItems() ? $this->cart->getItems() : array();
            $cartItemsSku = [];
            foreach ($items as $item) {
                $cartItemsSku[$item->getSku()] = $item->getSku();
            }

            foreach ($filteredListProductSkus as $key => $listProduct) {
                $locationCode = '';
                // get selected branch if branch pickup is active

                //remove uom separator
                $realKey = explode('_', $key);
                array_pop($realKey);
                $realKey = implode('_', $realKey);
                $delimiter = $this->listsMessagingCustomerHelper->getUOMSeparator();
                $sku = implode(' ', explode($delimiter, $key));
                if (!array_key_exists($realKey, $cartItemsSku)) {
                    //ensure cart item is added
                    $options['force'] = true;
                }
                $product = $this->productFactory->create();
                $product->load($product->getIdBySku($realKey));

                //if configurator product, reject with message
                if ($product->getEccConfigurator()) {
                    $this->message->addWarningMessage(
                        "Unable to add Product \"$sku\" to cart as it requires configuring");
                    continue;
                }

                $listProductDetails = $listProductSkus[$key];
                $options['qty'] = $listProductDetails->getQty();
                //if no selected branch, use default location
                $locationCode = $listProductDetails->getLocationCode();
                if (!($locationCode)) {
                    if ($this->branchPickupHelper->isBranchPickupAvailable()) {
                        $locationCode = $this->branchPickupHelper->getSelectedBranch();
                    }
                    if (!($locationCode)) {
                        $locationCode = $this->commLocationsHelper->getDefaultLocationCode();
                    }
                }
                $locationMessage = '';
                if ($this->commLocationsHelper->isLocationsEnabled()) {

                    //remove uom separator
                    $delimiter = $this->listsMessagingCustomerHelper->getUOMSeparator();

                    $productLocations = array_flip(array_keys($product->getLocations()));

                    //if no product locations, put out warning msg and continue to next product
                    if (empty($productLocations)) {
                        $this->message->addWarningMessage(
                            "Unable to add Product \"$sku\" to cart as no location available");
                        continue;
                    }
                    //if location code populated (by branch pickup or default code),
                    // check if valid for product before using, else use first product location
                    if (!empty($locationCode)) {
                        if (array_key_exists($locationCode, $productLocations)) {
                            //use default location if available
                            $options['location_code'] = $locationCode;
                        } else {
                            // if product not available on default erp location, display warning and continue
                            $options['location_code'] = array_keys($productLocations)[0];
                            $this->message->addWarningMessage(
                                "Unable to add Product \"$sku\" to cart. Not stocked at location " . $locationCode);
                            continue;
                        }
                    } else {
                        //if no default erp error
                        $this->message->addWarningMessage(
                            "Unable to add Product \"$sku\" to cart. No default location specified");
                        continue;
                    }

                }
                $this->cart->addProduct($product, $options);
                unset($options);
            }
            if (!$this->cart->getQuote()->getHasError()) {
                if ($this->listName) {
                    $this->message->addSuccessMessage("List $this->listName added to cart");
                } else {
                    $this->message->addErrorMessage("Invalid List");
                }
            }

        } catch (\Exception $e) {
            $this->message->addErrorMessage($e->getMessage());
            $this->message->addErrorMessage("Unable to add List $this->listName to cart at this time");
        }
    }

    private function setPositionOrder($collection)
    {
        if ($this->positionOrder) {
            $this->positionOrder->setPositionOrderByConfig($collection);
        }
    }
}
