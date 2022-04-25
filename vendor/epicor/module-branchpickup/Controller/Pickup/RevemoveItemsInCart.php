<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Controller\Pickup;

class RevemoveItemsInCart extends \Epicor\BranchPickup\Controller\Pickup
{



    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutCartHelper;

    /**
     * @var \Epicor\BranchPickup\Helper\Branchpickup
     */
    protected $branchPickupBranchpickupHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Helper\Cart $checkoutCartHelper,
        \Epicor\BranchPickup\Helper\Branchpickup $branchPickupBranchpickupHelper
    ) {
        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->branchPickupBranchpickupHelper = $branchPickupBranchpickupHelper;
        parent::__construct(
            $context,$branchPickupHelper,$customerSession
        );
    }


/**
     * Remove the items in the cart(After user confirmation), If the item is available for the pickup location
     * return boolean
     */
    public function execute()
    {
        //M1 > M2 Translation Begin (Rule p2-6.1
        /*$postValues = Mage::App()->getRequest()->getParam('removeitems');
        $branch = Mage::App()->getRequest()->getParam('branch');*/
        $postValues = $this->_request->getParam('removeitems');
        $branch = $this->_request->getParam('branch');
        //M1 > M2 Translation End
        $separateItems = explode(',', $postValues);
        $cartHelper = $this->checkoutCartHelper;
        $items = $cartHelper->getCart()->getItems();
        foreach ($items as $item) {
            if (in_array($item->getProduct()->getId(), $separateItems)) {
                $itemId = $item->getItemId();
                $cartHelper->getCart()->removeItem($itemId)->save();
            }
        }
        $helperBranchLocation = $this->branchPickupBranchpickupHelper;
        /* @var  Epicor_BranchPickup_Helper_Branchpickup */
        $helper = $this->branchPickupHelper;
        /* @var $helper Epicor_BranchPickup_Helper_Data */
        if ($branch && $helper->isValidLocation($branch)) {
            $helper->selectBranchPickup($branch);
            $helperBranchLocation->setBranchLocationFilter($branch);
        }
        $this->_redirect('checkout/cart');
    }

    }
