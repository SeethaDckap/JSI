<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\BranchPickup\ViewModel\Cart;

use Magento\Quote\Model\Quote;
use \Epicor\BranchPickup\Model\Carrier\Epicorbranchpickup;

/**
 * View Model Class for Estimate Tax & Shipping
 *
 * @category   Epicor
 * @package    Epicor_BranchPickup
 * @author     Epicor Websales Team
 */
class Shipping implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Epicor\Lists\Helper\Session
     */
    private $listSessionHelper;

    /**
     * @var Quote|null
     */
    private $quote = null;

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    private $branchPickupHelper;

    /**
     * Shipping constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Epicor\Lists\Helper\Session $listSessionHelper
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Lists\Helper\Session $listSessionHelper,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->listSessionHelper = $listSessionHelper;
        $this->branchPickupHelper = $branchPickupHelper;
    }

    /**
     * Get active quote
     *
     * @return Quote
     */
    public function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * Checks if branck pickup is selected
     *
     * @return bool
     */
    private function isBranchSelected()
    {
        $branchpickupEnabled = $this->branchPickupHelper->isBranchPickupAvailable();
        $selectedBranch = $this->listSessionHelper->getValue('ecc_selected_branchpickup');
        return $branchpickupEnabled && !empty($selectedBranch);
    }

    /**
     * Check if shipping method is selected
     *
     * @return bool
     */
    private function isShippingSelected()
    {
        $quote = $this->getQuote();
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        return !is_null($shippingMethod)
            && $shippingMethod != Epicorbranchpickup::ECC_BRANCHPICKUP_COMBINE;
    }

    /**
     * Check if Estimate Tax & Shipping method block can be shown
     *
     * @return bool
     */
    public function canShowEstimate()
    {
        $isBranchSelected = $this->isBranchSelected();
        $isShippingSelected = $this->isShippingSelected();
        return !$isBranchSelected || $isShippingSelected;
    }

    /**
     * Returns Lists session helper
     *
     * @return \Epicor\Lists\Helper\Session
     */
    public function getSessionHelper()
    {
        return $this->listSessionHelper;
    }
}