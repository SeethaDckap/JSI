<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Sales\Reorder;


/**
 * Reorder Sidebar override
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Sidebar extends \Magento\Sales\Block\Reorder\Sidebar
{
    /*
     * @var  \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        $this->commHelper = $commHelper;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $httpContext,
            $data
        );
    }

    public function isFunctionalityDisabledForCustomer($type)
    {
        $commHelper = $this->commHelper;
        $hidePrices = $commHelper->getEccHidePrice();
        return $commHelper->isFunctionalityDisabledForCustomer($type) || ($type == "cart" && $hidePrices && $hidePrices != 3);
    }

    /**
     * Check item product availability for reorder
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return boolean
     */
    public function isItemAvailableForReorder(\Magento\Sales\Model\Order\Item $orderItem)
    {
        $helper = $this->helper('epicor_comm');
        /* @var $helper Epicor_Comm_Helper_Data */

        if (!$helper->isFunctionalityDisabledForCustomer('cart')) {
            if ($orderItem->getProduct()) {
                //M1 > M2 Translation Begin (Rule 23)
                //return $orderItem->getProduct()->getStockItem()->getIsInStock();
                $product = $orderItem->getProduct();
                $stockItem = $this->catalogInventoryApiStockRegistryInterface->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                return $stockItem->getIsInStock();
                //M1 > M2 Translation End

            }
        }

        return false;
    }

    /**
     * Check item product availability for reorder
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return boolean
     */
    public function displayReorder()
    {
        if ($this->customerSession->getIsPunchout()) {
            return false;
        }
        return true;
    }

}
