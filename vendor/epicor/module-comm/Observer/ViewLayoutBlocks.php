<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ViewLayoutBlocks extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        return;                                        // remove to implement
        $req = $this->request;
        $info = sprintf(
            "\nRequest: %s\nFull Action Name: %s_%s_%s\nHandles:\n\t%s\nUpdate XML:\n%s", $req->getRouteName(), $req->getRequestedRouteName(), $req->getRequestedControllerName(), $req->getRequestedActionName(), implode("\n\t", $o->getLayout()->getUpdate()->getHandles()), $o->getLayout()->getUpdate()->asString()
        );

        // Force logging to var/log/layout.log
    }

}