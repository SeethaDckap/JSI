<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin\AccessRight;

use Epicor\Punchout\Plugin\AbstractPlugin;

class BranchPickupLink extends AbstractPlugin
{
    /**
     * Disable BranchPickup.
     *
     * @param Epicor\BranchPickup\Block\Link $subject
     * @param boolean $return
     * @return boolean
     */
    public function afterToHtml(\Epicor\BranchPickup\Block\Link $subject, $return)
    {
        $subject;
        if ($this->customerSession->getIsPunchout()) {
            $return = '';
        }
        return $return;
    }

}
