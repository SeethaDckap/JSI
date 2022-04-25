<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Controller\Pickup;

class RemoveItemsInCart extends \Epicor\BranchPickup\Controller\Pickup
{

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutCartHelper;

    /**
     * @var \Epicor\BranchPickup\Helper\Branchpickup
     */
    protected $branchPickupBranchpickupHelper;


    protected $registry;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Helper\Cart $checkoutCartHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\BranchPickup\Helper\Branchpickup $branchPickupBranchpickupHelper
    ) {
        $this->branchPickupHelper = $branchPickupHelper;
        $this->customerSession = $customerSession;
        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->branchPickupBranchpickupHelper = $branchPickupBranchpickupHelper;
        $this->registry = $registry;

        parent::__construct($context, $branchPickupHelper, $customerSession );
    }


/**
     * Remove the items in the cart(After user confirmation), If the item is available for the pickup location
     * return boolean
     */
    public function execute()
    {
        $postValues = $this->getRequest()->getParam('removeitems');
        $branch = $this->getRequest()->getParam('branch');

        $separateItems = explode(',', $postValues);
        $cartHelper = $this->checkoutCartHelper;
        $items = $cartHelper->getCart()->getItems();

        $helperBranchLocation = $this->branchPickupBranchpickupHelper;
        /* @var  Epicor_BranchPickup_Helper_Branchpickup */
        $helper = $this->branchPickupHelper;
        /* @var $helper Epicor_BranchPickup_Helper_Data */
        if ($branch && $helper->isValidLocation($branch)) {
            $helper->selectBranchPickup($branch);
            $helperBranchLocation->setBranchLocationFilter($branch);
        } else {
            $helper->selectBranchPickup(null);
            $helper->resetBranchLocationFilter();
            $helperBranchLocation->saveShippingInQuote(null);
        }

        foreach ($items as $item) {
            if (in_array($item->getProduct()->getId(), $separateItems)) {
                $itemId = $item->getItemId();
                $cartHelper->getCart()->removeItem($itemId);
            }
        }
        $quote = $cartHelper->getCart()->getQuote();
        $quote->collectTotals()->save();

        $this->_redirect('checkout/cart');
    }

    }
