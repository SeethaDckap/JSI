<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin;

use Magento\Framework\UrlInterface;

class AuthorizationLink extends AbstractPlugin
{

    /**
     * Signout hrf Plugin.
     *
     * @param Magento\Customer\Block\Account\AuthorizationLink $subject
     * @param string $return
     * @return string
     */

    public function afterGetHref(\Magento\Customer\Block\Account\AuthorizationLink $subject, $return)
    {
        if ($this->customerSession->getIsPunchout() && $subject->isLoggedIn()) {
            $return = $this->getLogoutUrl();
        }
        return $return;
    }


    /**
     * Signout label Plugin.
     *
     * @param Magento\Customer\Block\Account\AuthorizationLink $subject
     * @param string $return
     * @return string
     */
    public function afterGetLabel(\Magento\Customer\Block\Account\AuthorizationLink $subject, $return)
    {
        if ($this->customerSession->getIsPunchout() && $subject->isLoggedIn()) {
            $return = __('Cancel Punchout');
        }
        return $return;
    }


    /**
     * Retrieve customer logout url
     *
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->urlBuilder->getUrl('punchout/punchout/logout');
    }
}
