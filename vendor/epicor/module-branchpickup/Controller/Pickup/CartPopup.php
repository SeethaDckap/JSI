<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Controller\Pickup;

class CartPopup extends \Epicor\BranchPickup\Controller\Pickup
{
    
    /**
     * Shows the cart popup, If the item is available for the pickup location
     * return html
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->getResponse()->setBody($this->_view->getLayout()
                            ->createBlock('Epicor\BranchPickup\Block\Cart\Sidebar')
                            ->setTemplate('Epicor_BranchPickup::epicor/branchpickup/popup/itempopup.phtml')->toHtml());
    }
    
}