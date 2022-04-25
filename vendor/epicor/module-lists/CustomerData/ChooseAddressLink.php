<?php

/**
 * Copyright © 2010-2019 Epicor Software. All rights reserved.
 */

namespace Epicor\Lists\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Context;

class ChooseAddressLink implements SectionSourceInterface {

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
     * ChooseAddressLink constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Epicor\Comm\Helper\Data $commHelper
     * @param \Epicor\Comm\Model\Customer $customer
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Epicor\AccessRight\Model\Authorization $authorization
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\Customer $customer,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Registration $registration,
        \Epicor\AccessRight\Model\Authorization $authorization
    ) {
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        $this->customer = $customer;
        $this->httpContext = $httpContext;
        $this->_registration = $registration;
        $this->_accessauthorization = $authorization;

    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        $accountSummaryInfo = array("is_enable" => false);
        $isAccessAllow = $this->_accessauthorization->isAllowed(\Epicor\Lists\Block\Addresses\Link::FRONTEND_RESOURCE);
        $isTopLinkAllowed = $this->isChangeAddressAllowed();
        if ($isTopLinkAllowed && (!$this->_registration->isAllowed() || $this->httpContext->getValue(Context::CONTEXT_AUTH)) && $isAccessAllow) {
            $accountSummaryInfo["is_enable"] = true;
        }

        // Sales-rep M234 certification
        if($isTopLinkAllowed === null) {
            unset($accountSummaryInfo["is_enable"]);
        }

        return $accountSummaryInfo;
    }

    /**
     * Use Reference \Epicor\Lists\Observer\ModifyBlockHtmlBefore
     * with $this->>topLinkAllowed
     *
     * @return bool|null
     */
    public function isChangeAddressAllowed()
    {
        $customerId = $this->customerSession->getCustomerId();
        $customer = $this->customer->load($customerId);
        $commHelper = $this->commHelper;
        if ($customer->isSalesRep() && $commHelper->isMasquerading() == false) {
            return null;
        }
        if ($customer->isSupplier()) {
            return false;
        }

        return true;
    }
}