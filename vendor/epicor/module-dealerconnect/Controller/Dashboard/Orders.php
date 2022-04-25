<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Dashboard;

class Orders extends \Epicor\Dealerconnect\Controller\Grid
{

    const FRONTEND_RESOURCE = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    public function execute()
    {
        $this->setGridDashboardConfigration();
        $result = $this->resultLayoutFactory->create();
        $this->getResponse()->setBody(
            $result
                ->getLayout()
                ->createBlock('Epicor\Dealerconnect\Block\Customer\Dashboard\Orders\Grid')
                ->toHtml()    // location of grid block
        );
    }

}
