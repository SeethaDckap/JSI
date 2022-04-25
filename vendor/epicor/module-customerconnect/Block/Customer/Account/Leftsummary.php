<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Account;
use Magento\Framework\View\Element\Template;


/**
 * Customer ERP Account Summary Block
 *
 * @author gareth.james
 */
class Leftsummary extends \Epicor\Customerconnect\Block\Customer\Account\Summary
{




    public function toHtml()
    {
        if (!$this->_accessauthorization->isAllowed(
            'Epicor_Checkout::checkout_account_summary'
        )) {
            return '';
        }
        return \Magento\Framework\View\Element\Template::toHtml();
    }
}
