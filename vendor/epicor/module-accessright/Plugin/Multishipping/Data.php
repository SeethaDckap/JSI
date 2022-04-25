<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Plugin\Multishipping;

class Data {

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Epicor\AccessRight\Helper\Data $authorization
    ){

        $this->_accessauthorization = $authorization->getAccessAuthorization();

    }

    public function afterIsMultishippingCheckoutAvailable(
        \Magento\Multishipping\Helper\Data $subject,
        $result
    ) {
        if ($result) {
            if (!$this->_accessauthorization->isAllowed('Epicor_Checkout::checkout_checkout_multi_address')) {
                return false;
            }
        }
        return $result;
    }

}
