<?php

/**
 * Copyright © 2010-2019 Epicor Software. All rights reserved.
 */

namespace Epicor\Dealerconnect\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class DelearShopperLink implements SectionSourceInterface
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealerHelper;

    /**
     * DelearShopperLink constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Epicor\Dealerconnect\Helper\Data $dealerHelper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Dealerconnect\Helper\Data $dealerHelper
    )
    {
        $this->customerSession = $customerSession;
        $this->dealerHelper = $dealerHelper;
    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        $accountSummaryInfo = array("is_enable" => false);
        $erpaccountData = $this->customerSession->getCustomer()->getErpAcctCounts();
        if (!$this->customerSession->getMasqueradeAccountId() &&
            $erpaccountData && is_array($erpaccountData) &&
            count($erpaccountData) > 1) {
            return $accountSummaryInfo;
        }
        $isDealer = $this->dealerHelper->dealerLoggedIn();
        $toggleAllowed = $this->dealerHelper->checkCustomerToggleAllowed();
        if ($isDealer && $toggleAllowed !== "disabletoggle") {
            $accountSummaryInfo["is_enable"] = true;
        }
        return $accountSummaryInfo;
    }
}