<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Skus;

class Index extends \Epicor\Customerconnect\Controller\Skus
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_skus_read';

    public function execute()
    {
        if (!$this->customerSession->authenticate()) {
            return;
        }

        $result = $this->resultPageFactory->create();

        return $result;
    }

}
