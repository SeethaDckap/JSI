<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Plugin;

use Magento\Shipping\Model\Order\Track;

class TrackingNumberDetail
{
    public function afterGetNumberDetail(Track $subject, $result)
    {
        if (is_array($result)) {
            $result['track_id'] = $subject->getEntityId();
        }

        return $result;
    }
}