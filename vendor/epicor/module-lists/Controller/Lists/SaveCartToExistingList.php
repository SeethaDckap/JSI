<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Lists;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Math\Random;

class SaveCartToExistingList extends \Epicor\Lists\Controller\Lists\SaveCartAsList
{
    /**
     * /**
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
    protected $registry;

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
        \Magento\Framework\View\LayoutFactory $layoutFactory
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
            $checkoutCartHelper,
            $addProductToLists,
            $scopeConfig,
            $random,
            $orderFactory,
            $orderCollectionFactory,
            $layoutFactory
        );

        $this->random = $random;
        $this->urlBuilder = $context->getUrl();
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->layoutFactory = $layoutFactory;
        $this->registry = $commHelper->getRegistry();
    }


    public function execute()
    {
        $cartItems = json_decode(base64_decode(strtr($this->getRequest()->getParam('cartItems'), '-_', '+/')), true);
        $info = json_decode(base64_decode(strtr($this->getRequest()->getParam('info'), '-_', '+/')), true);

        $cartItems = $this->getItems($cartItems);
        if (isset($info['type']) && $info['type'] == 'product') {
            $id = $this->getRequest()->getParam('id', null);
        } else {
            $id = base64_decode($this->getRequest()->getParam('id', null));
        }
        $list = $this->listsListModelFactory->create()->load($id);
        // update list products
        if (!$list->isObjectNew()) {
            $data = [];
            $existingListProducts = $this->retrieveListProducts($list);
            $mergedListProducts = array_merge($existingListProducts, $cartItems);
            $data = $this->saveNewCartToListProducts($list, $mergedListProducts, $data);
            $data['saveCartToExistingList'] = 1;
            $this->processProductsQtySave($list, $data);
            if (isset($info['type']) && $info['type'] == 'product') {
                $returnUrl = $info['returnUrl'];
                $productname = $info['productname'];
                $this->messageManager->addSuccessMessage(__('Product  %1 has been successfully added to %2', $productname, $list->getTitle()));
                return $this->resultRedirectFactory->create()->setUrl($returnUrl);
            } else {
                $this->_redirect('*/*/edit', array(
                    'id' => base64_encode($list->getId())
                ));
            }
        }
        //redirect to lists/lists/edit
    }

    public function retrieveListProducts($list)
    {
        $collection = $this->listProductCollectionFactory->create();
        $collection->addFieldToFilter('list_id', $list->getId());
        $listProducts = [];
        foreach ($collection as $key => $product) {
            $listProducts[$key]['sku'] = $product->getSku();
            //if list is not type Fa, remove qty from cartItems
            $listProducts[$key]['qty'] = $list->getType() == 'Fa' ? $product->getQty() : null;
            $listProducts[$key]['uom'] = $product->getUom();
            $listProducts[$key]['location_code'] = $product->getLocationCode();
        }
        return $listProducts;
    }

}
