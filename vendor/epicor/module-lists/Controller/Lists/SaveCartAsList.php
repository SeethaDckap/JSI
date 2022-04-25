<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Lists;

use Magento\Framework\Math\Random;

class SaveCartAsList extends \Epicor\Lists\Controller\Lists\Save
{
    protected $productinfo = 'productinfo';

    protected $configSimpleProductId = false;

    protected $noQty = false;
    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutCartHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /*
    * Current Cart Items
    */
    protected $currentCartItems = []; /*
    /*
     * Cart Items and Qty
     */
    protected $cartItemsQty = [];/*
    /* List code
    */
    protected $listCode;
    /**
     * @var Random
     */
    protected $random;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    protected $listName;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListProductPositionFactory
     */
    private $listProductPositionFactory;
    /**
     * @var \Epicor\Lists\Model\AddProductToLists
     */
    private $addProductToLists;

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
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory $listProductCollectionFactory,
        \Epicor\Lists\Model\ListModel\Product $listProduct,
        \Magento\Checkout\Helper\Cart $checkoutCartHelper,
        \Epicor\Lists\Model\AddProductToLists $addProductToLists,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Math\Random $random,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Epicor\Lists\Model\ResourceModel\ListProductPositionFactory $listProductPositionFactory = null
    )
    {
        $this->scopeConfig = $scopeConfig;
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
            $timezone,
            $customerCustomerFactory,
            $listProductCollectionFactory,
            $listProduct,
            $orderFactory,
            $checkoutCartHelper
        );

        $this->random = $random;
        $this->urlBuilder = $context->getUrl();
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->layoutFactory = $layoutFactory;
        $this->registry = $commHelper->getRegistry();
        $this->listProductPositionFactory = $listProductPositionFactory;
        $this->addProductToLists = $addProductToLists;

    }//end __construct()


    public function execute()
    {
        $saveType = $this->getRequest()->getParam('selectedOption');
        $webOrderNumber = $this->getRequest()->getParam('webOrderNumber');
        $erpOrderNumber = $this->getRequest()->getParam('erpOrderNumber');
        switch (true) {
            case $webOrderNumber:
                $order = $this->orderFactory->create()->loadByIncrementId($webOrderNumber);
                $items = $order->getItems();
                $this->cartItemsQty['order'] = $order->getId();
                break;
            case  $erpOrderNumber:
                $order = $this->orderCollectionFactory->create()
                    ->addFieldToFilter('ecc_erp_order_number', array('eq' => $erpOrderNumber))->getFirstItem();
                $items = $order->getItems();
                $this->cartItemsQty['order'] = $order->getId();
                break;
            default:
                $cartHelper = $this->checkoutCartHelper;
                $items = $cartHelper->getCart()->getItems();
                $this->cartItemsQty['quote'] = $cartHelper->getCart()->getQuote()->getId();
                break;
        }
        if ($saveType == 4) {
            $productname = $this->addProductToLists();
            if ($this->noQty) {
                $returnDetails = array('status' => 'error', 'errormessage' => __('Please select a valid quantity for this product'));
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->getResponse()->setBody(json_encode($returnDetails));
                return;
            }
        }
        $this->listName = '';
        $redirect = '';
        $this->listCode = 'Cartsavedlist_' . $this->random->getRandomNumber();
        $listGrid = '';
        $revisedListGrid = '';
        switch ($saveType) {
            case '2':
                //Advanced Save
                $this->cartItemsQty['listCode'] = $this->listCode;
                $redirect = '/lists/lists/new/';
                break;
            case '3':
                //Existing List
                $listGrid = $this->layoutFactory->create()->createBlock('Epicor\Lists\Block\Customer\Account\Listing\Grid')->toHtml();
                //turn products on cart/order to string
                $encodedCartItems = "/lists/lists/saveCartToExistingList/cartItems/" . strtr(base64_encode(json_encode($this->cartItemsQty)), '+/', '-_');
                //replace the grid action url
                $revisedListGrid = str_replace("/lists/lists/edit", $encodedCartItems, $listGrid);
                //replace action id
                $revisedListGrid = str_replace('<a id="edit"', '<a id ="saveCartToList"', $revisedListGrid);
                //replace text in link
                $revisedListGrid = str_replace("Edit</a>", "Save Cart To List</a>", $revisedListGrid);
                //remove separator
                $revisedListGrid = str_replace("</a> | <a", "</a>  <a", $revisedListGrid);
                //disable delete action
                $revisedListGrid = str_replace('<a id="delete"', '<a id="delete" style="display: none;" ', $revisedListGrid);
                //remove onclick
                $revisedListGrid = str_replace('onclick="return window.confirm(\'Are you sure you want to delete this List? This cannot be undone\' )"> Delete', '', $revisedListGrid);
                break;
            case '4':
                //Existing List
                $returnUrl = $this->getRequest()->getParam('addToCartReturnUrl');
                $info = [
                    'type' => 'product',
                    'productname' => $productname,
                    'returnUrl' => "$returnUrl"
                ];
                //turn products on cart/order to string

                $encodedCartItems = "lists/lists/saveCartToExistingList/info/" . strtr(base64_encode(json_encode($info)), '+/', '-_') . "/cartItems/" . strtr(base64_encode(json_encode($this->cartItemsQty)), '+/', '-_');
                $this->customerSession->setAddtoListsUrl($encodedCartItems);
                $revisedListGrid = $this->layoutFactory->create()->createBlock('Epicor\Lists\Block\Customer\Account\Listing\AddtoGrid')->setEditUrl($encodedCartItems)->toHtml();

                break;
            default:
                //Quick Save
                foreach ($items as $key => $item) {
                    if ($item->getQtyOrdered()) {
                        $item->setQty($item->getQtyOrdered());
                    }
                    $this->handleDuplicateProd($item, $key);
                }
                $this->_quickSave();
                break;
        }
        $cartItems = base64_encode(json_encode($this->cartItemsQty));
        $returnDetails = array('status' => 'success', 'list' => $this->listName . '</br> Reference Code: ' . $this->listCode, 'redirect' => $redirect, 'redirect_parms' => $cartItems, 'listGrid' => $revisedListGrid);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($returnDetails));
    }

    /*
     * Add product To Lists
     */
    protected function addProductToLists()
    {
        $this->cartItemsQty = [];
        $page = $this->getRequest()->getParam('page');
        if ($page == 'detailedpage') {
            $params = $this->getRequest()->getParams();
            $this->processAddtoList();
            $productname = isset($params[$this->productinfo]['productname']) ? $params[$this->productinfo]['productname'] : '';

        } else {
            $items = $this->getRequest()->getParam($this->productinfo);
            $productname = isset($items['productname']) ? $items['productname'] : '';
            unset($items['productname']);
            foreach ($items as $key => $item) {
                $this->cartItemsQty[$key] = $item;
            }
        }
        return $productname;

    }

    /*
     * process products from details page
     */
    protected function processAddtoList()
    {
        $products = [];
        $skusids = [];
        $params = $this->getRequest()->getParams();
        $productIds = isset($params[$this->productinfo]['products']) ? $params[$this->productinfo]['products'] : [];
        $superGroup = isset($params[$this->productinfo]['super_group_locations']) ? $params[$this->productinfo]['super_group_locations'] : [];
        $this->configSimpleProductId = isset($params[$this->productinfo]['selected_configurable_option']) ? $params[$this->productinfo]['selected_configurable_option'] : false;

        if (!empty($productIds) || !empty($superGroup)) {

            if (!empty($productIds)) {
                $multidata = $this->_addmultiple($productIds, $params);
                $products = $multidata[0];
                $skusids = $multidata[1];
            } else {
                $superdata = $this->_addSuperGroup($superGroup);
                $products = $superdata[0];
                $skusids = $superdata[1];
            }
        } else {
            $simpledata = $this->simpleAddtoList($params);
            $products = $simpledata[0];
            $skusids = $simpledata[1];
        }
        if (empty($products)) {
            $this->noQty = true;
            return;
        }
        $skus = $this->addProductToLists->getSkuByIds(array_keys($skusids));

        $storeId = $this->addProductToLists->getStoreManager()->getStore()->getStoreId();
        foreach ($products as $key => $item) {
            $item['sku'] = $skus[$item['product_id']]['sku'];
            $item['uom'] = $this->addProductToLists->catalogResourceModelProductFactory()
                ->getAttributeRawValue($item['product_id'], 'ecc_default_uom', $storeId);
            $this->cartItemsQty[$key] = $item;
        }

    }

    /*
     * process simple products
     */
    protected function simpleAddtoList($params)
    {
        $products = [];
        $skusids = [];
        $productId = $params[$this->productinfo]['product'];
        if ($this->configSimpleProductId) {
            $productId = $this->configSimpleProductId;
        }
        $skusids[$productId] = $productId;
        if (!isset($params[$this->productinfo]['qty']) || ($params[$this->productinfo]['qty'] == 0)) {
            $qty = 1;
        } else {
            $qty = $params[$this->productinfo]['qty'];
        }

        $locHelper = $this->addProductToLists->getLocationsHelper();
        $locEnabled = $locHelper->isLocationsEnabled();
        $location_code = '';
        if ($locEnabled) {
            $location_code = $locHelper->getDefaultLocationCode();
            if (isset($params[$this->productinfo]['location_code'])) {
                $location_code = $params[$this->productinfo]['location_code'];
            }
        }
        $products[] = [
            'qty' => $qty,
            'location_code' => $location_code,
            'product_id' => $productId
        ];
        return [$products, $skusids];
    }

    /*
     * process multi products
     */
    protected function _addmultiple($productIds, $params = array())
    {
        $products = [];
        $skusids = [];
        foreach ($productIds as $productId => $request) {
            $productId = str_replace("products[", "", $productId);
            if ($this->configSimpleProductId) {
                $productId = $this->configSimpleProductId;
            }
            if (isset($request['multiple'])) {
                foreach ($request['multiple'] as $mRequest) {
                    $skusids[$productId] = $productId;
                    $qty = $mRequest['qty'];
                    if (!$qty) {
                        continue;
                    }
                    $location_code = $mRequest['location_code'];
                    $products[] = [
                        'qty' => $qty,
                        'location_code' => $location_code,
                        'product_id' => $productId
                    ];

                }
            }
        }
        return [$products, $skusids];
    }

    /*
     * process super group products
     */
    protected function _addSuperGroup($superGroup)
    {
        $products = [];
        $skusids = [];
        foreach ($superGroup as $locationCode => $group) {
            $locationCode = str_replace("super_group_locations[", "", $locationCode);
            if (is_array($group)) {
                foreach ($group as $productId => $qty) {
                    $skusids[$productId] = $productId;
                    if (!$qty) {
                        continue;
                    }
                    $location_code = $locationCode;
                    $products[] = [
                        'qty' => $qty,
                        'location_code' => $location_code,
                        'product_id' => $productId
                    ];
                }
            } else {
                if (!$group) {
                    continue;
                }
                $productId = str_replace("super_group[", "", $locationCode);
                $skusids[$productId] = $productId;
                $products[] = [
                    'qty' => $group,
                    'location_code' => '',
                    'product_id' => $productId
                ];

            }
        }
        return [$products, $skusids];
    }

    protected function _quickSave()
    {

        $this->getRequest()->setPostValue('selected_products', $this->currentCartItems);
        $list = $this->listsListModelFactory->create();
        $this->listName = $this->scopeConfig->getValue('epicor_lists/savecartaslist/defaultlistname') . '_' . date("Y-m-d-H.i.s", time());
        $data = ['title' => $this->listName,
            'notes' => 'Save Cart As List',
            'description' => 'Save Cart as List',
            'priority' => '0',
            'active' => '1',
            'source' => 'Customer',
            'erp_code' => $this->listCode,
            'type' => 'Fa',
            'selected_products' => $this->currentCartItems
        ];
        $list->setErpAccountsExclusion();
        $list->setErpAccountLinkType('E');
        $this->processDetailsSave($list, $data);
        $valid = $list->validate(true);
        //validation check not necessary as will always be set up properly
        if ($valid === true) {
            $customer = $this->customerCustomerFactory->create()->load($this->customerSession->getId());
            $erpAccount = $this->commHelper->getErpAccountInfo();
            $customerAccountType = $customer->getEccErpAccountType();
            if ($customerAccountType == "guest") {
                $list->setErpAccountLinkType('N');
            } else {
                $list->addErpAccounts(array(
                    $erpAccount->getid()
                ));
            }

            if ($list->isObjectNew()) {
                $list->setOwnerId($this->customerSession->getId());
                $list->setSource('customer');
            }
            $list->save();

            // When a master shopper creates or edits a list he should not be assigned to the list automatically.
            //It is up to the master shopper to decide whether he wants to assign himself.
            if ($customer->getEccMasterShopper() == false) {
                $list->addCustomers(array($customer->getId()));
            }

            $this->processCustomersSave($list, $data);
            $this->processProductsSave($list, $data);
            $data['saveCartToAdvancedList'] = 1;
            $this->processProductsQtySave($list, $data);

            $list->save();
            $cartItems = $data['selected_products'] ?? [];
            if ($this->listProductPositionFactory) {
                /** @var \Epicor\Lists\Model\ResourceModel\ListProductPosition $listPosition */
                $listPosition = $this->listProductPositionFactory->create();
                $listPosition->savePositionOrder($list, $cartItems);
            }
        }
    }

    /**
     * Save Products Information
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param array $data
     *
     * @return void
     */
    protected function saveProducts(&$list, $data)
    {
        $products = $data['selected_products'];
        $list->addProducts($products);
    }


    /**
     * Handle duplicate products.
     *
     * @param $items
     * @param $item
     * @param $key
     */
    private function handleDuplicateProd($item, $key)
    {
        $listPositionObj = $this->listProductPositionFactory->create();
        $getQty          = $listPositionObj->getQuantityDetails($item);
        $isLocationSame  = $listPositionObj->getLocationDetails($item);
        if ($getQty) {
            $item->setQty($getQty);
        }

        if ($isLocationSame) {
            $this->currentCartItems[$item->getProductId()] = $item;

        } else {
            $this->currentCartItems[$key] = $item;
        }

    }//end handleDuplicateProd()


}
