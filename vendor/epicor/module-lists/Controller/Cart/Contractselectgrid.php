<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Cart;

class Contractselectgrid extends \Epicor\Lists\Controller\Lists
{

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
        /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    
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
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Framework\Registry $registry
    )
    {
        $this->checkoutCart = $checkoutCart;
        $this->registry = $registry;
        $this->layoutFactory = $layoutFactory;
        
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
     * Contract Select Page
     *
     * @return void
     */
    public function execute()
    {
        $itemId = $this->getRequest()->getParam('itemid');
        $cart = $this->checkoutCart;
        /* @var $cart Mage_Checkout_Model_Cart */

        $cartItem = $cart->getQuote()->getItemById($itemId);
        /* @var $cartItem Mage_Sales_Model_Quote_Item */
        $this->registry->register('ecc_line_contract_item', $cartItem);
        $output = $this->getLayoutFactory()->create()->createBlock('Epicor\Lists\Block\Cart\Contract\Select\Grid')->toHtml();
        $this->getResponse()->appendBody($output);
    }
    
    /**
     * @return \Magento\Framework\View\LayoutFactory
     */
    public function getLayoutFactory() {
        return $this->layoutFactory;
    }

}
