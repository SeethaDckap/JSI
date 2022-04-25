<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin;

class MyAccountLinkPlugin {
    /*
     * @var \Epicor\Comm\Helper\Data
     */

    protected $commHelper;

    public function __construct(
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Framework\UrlInterface $url,
            \Magento\Customer\Model\Session $customerSession,
            \Epicor\Comm\Model\GlobalConfig\Config $globalConfig,
            \Epicor\Comm\Helper\Data $commHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->globalConfig = $globalConfig;
        $this->customerSession = $customerSession;
        $this->url = $url;
        $this->commHelper=$commHelper;
    }

    /**
     * Get all types to extensions map including log files extensions
     *
     * @return array
     */
    public function afterGetHref(\Magento\Customer\Block\Account\Link $subject, $result) {
        $customer = $this->customerSession->getCustomer();
        $dashboard = 'comm';
        if ($customer->isSalesRep()) {
            $dashboard = 'salesrep';
        } elseif ($customer->isCustomer()) {
            $dashboard = $this->scopeConfig->getValue('Epicor_Comm/dashboard_priority/dashboard', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } elseif ($customer->isSupplier()) {
            $dashboard = 'supplierconnect';
        }
        if ($dashboard == 'accounttypedashboard') {
            $dashboard = $this->getAccountDashboardType($customer, $dashboard);
        }
        if ($dashboard != 'comm') {
            $result = $this->url->getUrl($this->globalConfig->get("xml_{$dashboard}_dashboard/path"));
        }
        return $result;
    }

    /**
     * redirect customer after logging to respective dashboard
     */
    public function getAccountDashboardType($customer, $dashboard) {
        $erpAccount = $this->commHelper->getErpAccountInfo();
        $custType = $erpAccount->getAccountType();
        if ($customer->isSalesRep()) {
            $dashboard = 'salesrep';
        } elseif ($custType == 'B2B') {
            $dashboard = 'customerconnect';
        } elseif ($custType == 'B2C') {
            $dashboard = 'comm';
        } elseif ($customer->isSupplier()) {
            $dashboard = 'supplierconnect';
        } else if ($customer->isDealer()) {
            $dashboard = 'dealerconnect';
        } elseif ($customer->isDistributor()) {
            $dashboard = 'customerconnect';
        }
        return $dashboard;
    }

}
