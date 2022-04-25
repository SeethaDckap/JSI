<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Plugin\Order;

class Reorder {

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Epicor\AccessRight\Helper\Data $authorization
    ){

        $this->_accessauthorization = $authorization->getAccessAuthorization();

    }

    public function afterCanReorder(
        \Magento\Sales\Helper\Reorder $subject,
        $result
    ) {
        if ($result) {
            if (!$this->_accessauthorization->isAllowed('Epicor_Customer::my_account_orders_reorder')) {
               return false;
            }
        }
        return $result;
    }

}
