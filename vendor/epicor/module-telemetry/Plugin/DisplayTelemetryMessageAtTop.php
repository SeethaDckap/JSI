<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Plugin;

use Epicor\Telemetry\Model\System\Message\TelemetryEnabled;
use Magento\AdminNotification\Ui\Component\DataProvider\DataProvider;

class DisplayTelemetryMessageAtTop
{
    /**
     * Moves Telemetry message to the top of the array.
     *
     * @param DataProvider $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetData(DataProvider $subject, array $result)
    {
        foreach ($result['items'] as $key => $item) {
            if ($item['identity'] == TelemetryEnabled::IDENTITY) {
                unset($result['items'][$key]);
                array_unshift($result['items'], $item);
            }
        }

        return $result;
    }
}
