<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Approvals;


class Action extends \Epicor\AccessRight\Block\Template
{
    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_approvals_approved_reject';

    /**
     * @return string
     */
    public function _toHtml()
    {
        if ($this->_isAllowed() === false) {
            return '';
        }

        return parent::_toHtml();
    }
}