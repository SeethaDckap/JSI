<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin\AccessRight;

use Epicor\Punchout\Plugin\AbstractPlugin;

class BranchPickupHelper extends AbstractPlugin
{

    /**
     * Disable BranchPickup.
     *
     * @param Epicor\BranchPickup\Helper\Data $subject
     * @param boolean $return
     * @return boolean
     */
    public function afterIsBranchPickupAvailable(\Epicor\BranchPickup\Helper\Data $subject, $return)
    {
        $subject;
        if ($this->customerSession->getIsPunchout()) {
            $return = false;
        }
        return $return;
    }

}
