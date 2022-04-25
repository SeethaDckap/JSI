<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Dashboard;

class Claims extends \Epicor\Dealerconnect\Controller\Grid
{
    public function execute()
    {
        $this->setGridDashboardConfigration();
        $result = $this->resultLayoutFactory->create();
        $this->getResponse()->setBody(
            $result
                ->getLayout()
                ->createBlock('Epicor\Dealerconnect\Block\Customer\Dashboard\Claims\Grid')
                ->toHtml()    // location of grid block
        );
    }

}
