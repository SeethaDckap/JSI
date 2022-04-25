<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Plugin\Block\Account;

class Address {

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Epicor\AccessRight\Helper\Data $authorization
    ){

        $this->_accessauthorization = $authorization->getAccessAuthorization();

    }

    public function afterToHtml(
        \Magento\Customer\Block\Account\Dashboard\Address $subject,
        $result
    ) {
        if ($result) {
            if(!$this->_accessauthorization->isAllowed('Epicor_Customer::my_account_address_book_read')) {
                return '';
            }
        }
        return $result;
    }

}
