<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class RemoveItemsInCart extends \Epicor\Lists\Controller\Lists
{

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutCartHelper;

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
        \Magento\Checkout\Helper\Cart $checkoutCartHelper
    ) {

        $this->checkoutCartHelper = $checkoutCartHelper;

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
    }
    /**
     * Remove the items in the cart(After user confirmation), If the item is not available for that delivery address
     * return boolean
     */
    public function execute()
    {
        $postValues = $this->getRequest()->getParam('removeitems');
        $shippingmethod = $this->getRequest()->getParam('shippingmethod');
        $addressId =  $this->getRequest()->getParam('addressid');
        $removeProducts = explode(',', $postValues);
        $cartHelper = $this->checkoutCartHelper;
        /* @var $cartHelper MAge_Checkout_Helper_Cart */
        $items = $cartHelper->getCart()->getItems();
        foreach ($items as $item) {
            if (in_array($item->getProduct()->getId(), $removeProducts)) {
                $itemId = $item->getItemId();
                $cartHelper->getCart()->removeItem($itemId);
            }
        }
        $quote = $cartHelper->getCart()->getQuote();
        if($shippingmethod){
            $quote->getShippingAddress()->setShippingMethod($shippingmethod);
        }
        $quote->collectTotals()->save();
        if ($addressId) {
            $listHelper = $this->listsFrontendRestrictedHelper;
            /* @var $listHelper Epicor_Lists_Helper_Frontend_Restricted */
            $listHelper->setRestrictionAddress($addressId);
        }
        $this->_redirect('checkout/cart',[]);
        /* comment for WSO-3375 */
//        $controller = Mage::App()->getRequest()->getParam('page');
//        if($controller =="chooseaddress") {
//           $this->_redirect('lists/list/deliveryaddress');
//        } else {
//          $this->_redirect('checkout/cart');
//        }
    }

    }
