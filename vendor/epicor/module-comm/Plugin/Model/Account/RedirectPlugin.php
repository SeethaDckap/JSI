<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Model\Account;

use Magento\Customer\Model\Account\Redirect;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;

class RedirectPlugin
{
    const LOGIN_LANDING_PAGE_CONFIG_PATH = 'epicor_common/login/landing_page';
    /**
     * @var CustomerSession
     */
    private $customerSession;
    /**
     * @var ScopeConfig
     */
    private $scopeConfig;

    public function __construct(
        CustomerSession $customerSession,
        ScopeConfig $scopeConfig
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Redirect $subject
     * @param $result
     * @return mixed
     */
    public function afterGetRedirectCookie(Redirect $subject, $result)
    {
        $beforeAuthUrl = $this->customerSession->getdata('before_auth_url');

        if (!$result && $beforeAuthUrl && $this->isConfigLastPageAccessed()
            && $this->isQuotesRequestUrl($beforeAuthUrl)) {
            return $beforeAuthUrl;
        }

        return $result;
    }

    /**
     * @param $url
     * @return bool
     */
    private function isQuotesRequestUrl($url)
    {
        $isQuotesRequest = preg_match('/epicor_quotes\/request/', $url);

        return $isQuotesRequest === 1;
    }

    /**
     * @return bool
     */
    private function isConfigLastPageAccessed()
    {
        return $this->scopeConfig->getValue(self::LOGIN_LANDING_PAGE_CONFIG_PATH) === 'last_page';
    }
}
