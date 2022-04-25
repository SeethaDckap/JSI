<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Plugin\Block\Account;

class Navigation {

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Epicor\AccessRight\Helper\Data $authorization
    ){

        $this->_accessauthorization = $authorization->getAccessAuthorization();

    }

    public function afterGetLinks(
        \Magento\Customer\Block\Account\Navigation $subject,
        $result
    ) {
        if ($result) {
            foreach($result  as $key => $link) {
                if($link->getPath() == 'customer/account'){
                    $link->setResource('Epicor_Customer::my_account_dashboard');
                }
                if($link->getPath() == 'customer/account/edit'){
                    $link->setResource('Epicor_Customer::my_account_information');
                }
                if($link->getPath() == 'customer/address'){
                    $link->setResource('Epicor_Customer::my_account_address_book');
                }


                if ($link->getResource() && !$this->_accessauthorization->isAllowed($link->getResource())) {
                    unset($result[$key]);
                }
            }
        }
        return $result;
    }

}
