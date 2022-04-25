<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Plugin;

use Epicor\ReleaseNotification\Model\System\Message\EccRelease;
use Magento\AdminNotification\Ui\Component\DataProvider\DataProvider;

class DisplayEccSystemMessageAtTop
{
    /**
     * Moves ECC message to the top of the array.
     *
     * @param DataProvider $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetData(DataProvider $subject, array $result)
    {
        foreach ($result['items'] as $key => $item) {
            if ($item['identity'] == EccRelease::IDENTITY) {
                unset($result['items'][$key]);
                array_unshift($result['items'], $item);
            }
        }

        return $result;
    }
}
