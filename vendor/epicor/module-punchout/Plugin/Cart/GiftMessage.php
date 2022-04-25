<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin\Cart;

use Epicor\Punchout\Plugin\AbstractPlugin;

class GiftMessage extends AbstractPlugin
{

    /**
     * Disable Gift message.
     *
     * @param Magento\GiftMessage\Model\GiftMessageConfigProvider $subject
     * @param boolean $return
     * @return boolean
     */
    public function afterGetConfig(\Magento\GiftMessage\Model\GiftMessageConfigProvider $subject, $return)
    {
        $subject;
        if ($this->customerSession->getIsPunchout()) {
            $return['isOrderLevelGiftOptionsEnabled'] = 0;
            $return['isItemLevelGiftOptionsEnabled'] = 0;
        }
        return $return;
    }

}
