<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Grid;

class Invoicessearch extends \Epicor\Customerconnect\Controller\Grid
{

    public function execute()
    {
        $result = $this->resultLayoutFactory->create();
        $this->getResponse()->setBody(
            $result->getLayout()->createBlock('Epicor\Customerconnect\Block\Customer\Dashboard\Invoices\Grid')->toHtml()    // location of grid block
        );
    }

}
