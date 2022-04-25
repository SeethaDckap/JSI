<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Lists;

class MassAddListToCart extends \Epicor\Lists\Controller\Lists\AddListToCart
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
    /**
     * @var \Epicor\Lists\Helper\Messaging\Customer
     */
    private $listsMessagingCustomerHelper;


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
        \Epicor\Lists\Helper\Messaging\Customer $listsMessagingCustomerHelper
    )
    {

        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->quote = $quote;
        $this->listProductCollectionFactory = $listProductCollectionFactory;
        $this->listProduct = $listProduct;
        $this->productFactory = $productFactory;
        $this->cart = $cart;
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
            $timezone,
            $checkoutCartHelper,
            $quote,
            $listProductCollectionFactory,
            $listProduct,
            $productFactory,
            $cart,
            $branchPickup,
            $listsMessagingCustomerHelper
        );
    }

    /**
     * Check if items in list are in the cart, if so update them, if not add them
     * return boolean
     */
    public function execute()
    {
        $listIds = [];
        $listIdsparam = $this->getRequest()->getParam('listid');
        if (!is_array($listIdsparam)) {
            $listIds = explode(',', $listIdsparam);
        } else {
            $listIds = $listIdsparam;
        }
        foreach ($listIds as $id) {
            $this->addToCart($id);
            $this->cart->setData('is_mass_list_add', 1);
            $this->cart->save();
        }
        $this->cart->unsetData('is_mass_list_add');
        $this->cart->getQuote()->setTotalsCollectedFlag(false);
        $this->cart->getQuote()->save();
        $this->cart->save();
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
