<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Log extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Epicor\ReleaseNotification\Model\Data\Log::class,
            \Epicor\ReleaseNotification\Model\ResourceModel\Log::class
        );
    }
}
