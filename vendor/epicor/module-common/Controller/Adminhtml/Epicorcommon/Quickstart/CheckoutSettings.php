<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Quickstart;

class CheckoutSettings extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Quickstart
{
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();

        return $resultLayout;
    }

}
