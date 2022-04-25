<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Plugin\Customer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

class Redirect
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Epicor\Comm\Model\GlobalConfig\Config
     */
    protected $globalConfig;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;
    /*
     * @var \Magento\Cms\Helper\Page
     */
    protected $cmsPageHelper;

    /*
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Cms\Helper\Page $cmsPageHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        RequestInterface $request
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->globalConfig = $globalConfig;
        $this->customerSession = $customerSession;
        $this->response = $response;
        $this->url = $url;
        $this->cmsPageHelper = $cmsPageHelper;
        $this->commHelper=$commHelper;
        $this->registry        = $commHelper->getRegistry();
        $this->request = $request;
    }

    /**
     * Get all types to extensions map including log files extensions
     *
     * @return array
     */
    public function afterGetRedirect(\Magento\Customer\Model\Account\Redirect $subject, $result)
    {
        $IsShopper = false;
        if ($result instanceof \Magento\Framework\Controller\Result\Redirect\Interceptor) {
            $customer = $this->customerSession->getCustomer();
            //M1 > M2 Translation End
            $dashboard = 'comm';
            if ($customer->isSalesRep()) {
                $dashboard = 'salesrep';
            } elseif ($customer->isCustomer()) {
                $IsShopper = true;
                //M1 > M2 Translation Begin (Rule p2-5.13)
                //$dashboard = Mage::getStoreConfig('Epicor_Comm/dashboard_priority/dashboard');
                $dashboard = $this->_scopeConfig->getValue('Epicor_Comm/dashboard_priority/dashboard', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                //M1 > M2 Translation End
            } elseif ($customer->isSupplier()) {
                $dashboard = 'supplierconnect';
            }else if($customer->isGuest()){
                $IsShopper = true;
            }else if($customer->getEccErpAccountType()=='guest'){
                $IsShopper = true;
            }
            if ($dashboard == 'accounttypedashboard') {
                $dashboard = $this->getAccountDashboardType($customer, $dashboard);
            }
            if ($this->_scopeConfig->isSetFlag('customer/startup/redirect_dashboard')
                || $this->request->getActionName() === "createpost") {
                $result->setUrl($this->url->getUrl($this->globalConfig->get("xml_{$dashboard}_dashboard/path")));
            }
            if($IsShopper && $redirectUrl = $this->getLoginPostRedirect()){
                $result->setUrl($redirectUrl);
            }
        }

        return $result;
    }

    /**
     * Define target URL and redirect customer after logging in
     */
    protected function getLoginPostRedirect()
    {
        $customerSession = $this->customerSession;

        if (strpos($customerSession->getBeforeAuthUrl(), 'onepage') == false &&
            strpos($customerSession->getBeforeAuthUrl(), 'multishipping') == false &&
            strpos($customerSession->getBeforeAuthUrl(), 'comm') == false) {

            if ($this->_scopeConfig->isSetFlag('customer/startup/redirect_dashboard')) {
                return false;
            }
            if ($this->_scopeConfig->getValue('epicor_common/login/landing_page',\Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'cms_page') {
                $cmsPageId = $this->_scopeConfig->getValue('epicor_common/login/landing_cms_page',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($cmsPageId) {
                    $url = $this->cmsPageHelper->getPageUrl($cmsPageId);
                    if ($url) {
                        return $url;
                    }
                }
            }
        }
        return false;

    }
    /**
     * redirect customer after logging to respective dashboard
     */
    public function getAccountDashboardType($customer, $dashboard) {
        $this->registry->register('after_login_msq', 1);
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
