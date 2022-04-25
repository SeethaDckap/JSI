<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Account;

use Magento\Customer\Helper\Address;
class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{
     /**
     * Retrieve success message from the system config 
     *
     * @return string
     */
    protected function getSuccessMessage()
    {
        $customWelcomeMessage = $this->scopeConfig->getValue('epicor_b2b/registration/customer_success_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (empty($customWelcomeMessage)) {
            return parent::getSuccessMessage();
        }

        if ($this->addressHelper->isVatValidationEnabled()) {
            if ($this->addressHelper->getTaxCalculationAddressType() == Address::TYPE_SHIPPING) {
                $message = __(
                    'If you are a registered VAT customer, please <a href="%1">click here</a> to enter your shipping address for proper VAT calculation.',
                    $this->urlModel->getUrl('customer/address/edit')
                );
            } else {
                $message = __(
                    'If you are a registered VAT customer, please <a href="%1">click here</a> to enter your billing address for proper VAT calculation.',
                    $this->urlModel->getUrl('customer/address/edit')
                );
            }
        } else {
            $message = __($customWelcomeMessage);
        }
        return $message;
    }
}
