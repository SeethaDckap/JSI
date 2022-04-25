<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Controller\Pickup;

class Location extends \Epicor\BranchPickup\Controller\Pickup
{
    
    
    
    public function execute()
    {
        $this->_view->loadLayout();
        $this->getResponse()->setBody($this->_view->getLayout()
                            ->createBlock('Epicor\BranchPickup\Block\Location\Edit')
                            ->setTemplate('Epicor_BranchPickup::epicor/branchpickup/popup/editlocation.phtml')->toHtml());
    }
    
}
