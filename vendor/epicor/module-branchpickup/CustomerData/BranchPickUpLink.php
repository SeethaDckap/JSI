<?php

/**
 * Copyright © 2010-2019 Epicor Software. All rights reserved.
 */

namespace Epicor\BranchPickup\CustomerData;

use Epicor\Dealerconnect\Observer\unsetCurrentPriceMode;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Context;

class BranchPickUpLink implements SectionSourceInterface {

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customer;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Model\Registration
     */
    protected $_registration;

    /**
     * @var \Epicor\SalesRep\Helper\Data
     */
    protected $salesRepHelper;

    /**
     * BranchPickUpLink constructor.
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Epicor\BranchPickup\Helper\Data $branchpickupHelper
     */
    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Registration $registration,
        \Epicor\BranchPickup\Helper\Data $branchpickupHelper,
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->httpContext = $httpContext;
        $this->_registration = $registration;
        $this->branchpickupHelper = $branchpickupHelper;
        $this->salesRepHelper = $salesRepHelper;
        $this->customerSession = $customerSession;

    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        $accountSummaryInfo = array("is_enable" => false);
        if (!$this->_registration->isAllowed() || $this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            if ($this->branchpickupHelper->isBranchPickupAvailable()) {
                $accountSummaryInfo["is_enable"] = true;
            }
        } else {
            if ($this->branchpickupHelper->isBranchPickupAvailable()) {
                $accountSummaryInfo["is_enable"] = true;
            }
        }

        // Sales-rep M234 certification
        if(!$accountSummaryInfo["is_enable"]) {
            $salesRepHelper = $this->salesRepHelper;
            /* @var $salesRepHelper Epicor_SalesRep_Helper_Data */

            $customerSession = $this->customerSession;
            /* @var $customerSession \Magento\Customer\Model\Session */

            $customer = $customerSession->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */

            if ($salesRepHelper->isEnabled() && $customer->isSalesRep() && !$salesRepHelper->isMasquerading()) {
                unset( $accountSummaryInfo["is_enable"]);
            }
        }

        return $accountSummaryInfo;
    }

}