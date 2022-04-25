<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin;

class AccountLink extends AbstractPlugin
{

    /**
     * My Account Link.
     *
     * @param Magento\Customer\Block\Account\Link $subject
     * @param boolean $return
     * @return boolean
     */
    public function afterToHtml(\Magento\Customer\Block\Account\Link $subject, $return)
    {
        $subject;
        if ($this->customerSession->getIsPunchout()) {
            $return = '';
        }
        return $return;
    }

}
