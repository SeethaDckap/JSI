<?php

/**
 * Copyright © 2010-2019 Epicor Software. All rights reserved.
 */

namespace Epicor\QuickOrderPad\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class QuickOrderPadLink implements SectionSourceInterface {

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\SalesRep\Helper\Data
     */
    protected $salesRepHelper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * QuickOrderPadLink constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Epicor\SalesRep\Helper\Data $salesRepHelper
     * @param \Epicor\AccessRight\Model\Authorization $authorization
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Epicor\AccessRight\Model\Authorization $authorization
    ) {
        $this->customerSession = $customerSession;
        $this->salesRepHelper = $salesRepHelper;
        $this->_accessauthorization = $authorization;
    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        $accountSummaryInfo = array("is_enable" => false);
        $isAccessAllow = $this->_accessauthorization->isAllowed(\Epicor\QuickOrderPad\Block\Link::FRONTEND_RESOURCE);
        $isTopLinkAllowed = $this->isTopLinkAllowed();

        if ($isTopLinkAllowed && $isAccessAllow) {
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
    public function isTopLinkAllowed()
    {
        $salesRepHelper = $this->salesRepHelper;
        /* @var $salesRepHelper Epicor_SalesRep_Helper_Data */
        $customerSession = $this->customerSession;
        /* @var $customerSession \Magento\Customer\Model\Session */
        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */
        if ($salesRepHelper->isEnabled() && $customer->isSalesRep() && !$salesRepHelper->isMasquerading()) {
            return null;
        }

        if ($customer->isSupplier()) {
            return false;
        }

        return true;
    }
}