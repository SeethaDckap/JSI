<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Arpayments;

class History extends \Epicor\Customerconnect\Controller\Arpayments
{
    const FRONTEND_RESOURCE = "Epicor_Customer::my_account_ar_payment_received_read";

    public function execute() {
         return $this->resultPageFactory->create();
    }

}
