<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin;

class EstimateShipping extends AbstractPlugin
{

    /**
     * After isAccessRigtsEnabled Plugin.
     *
     * @param Epicor\BranchPickup\ViewModel\Cart\Shipping $subject
     * @param boolean $return
     * @return boolean
     */
    public function afterCanShowEstimate(\Epicor\BranchPickup\ViewModel\Cart\Shipping $subject, $return)
    {
        $subject;
        if ($this->customerSession->getIsPunchout()) {
            $return = false;
        }
        return $return;
    }

}
