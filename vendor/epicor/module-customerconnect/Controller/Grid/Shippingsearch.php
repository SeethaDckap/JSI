<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Grid;

class Shippingsearch extends \Epicor\Customerconnect\Controller\Grid
{

    public function execute()
    {
        $this->recreateCUAD();

        $result = $this->resultLayoutFactory->create();

        $this->getResponse()->setBody(
            $result->getLayout()->createBlock('Epicor\Customerconnect\Block\Customer\Account\Shippingaddress\Listing\Grid')->toHtml()    // location of grid block
        );
    }

}
