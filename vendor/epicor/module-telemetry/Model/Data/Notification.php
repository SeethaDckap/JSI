<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\Data;

use Epicor\Telemetry\Api\Data\NotificationInterface;
use Magento\Framework\DataObject;

class Notification extends DataObject implements NotificationInterface
{
    /**
     * Columns names.
     */
    const ID = 'ID';
    const VIEWER_ID = 'viewer_id';

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getViewerId()
    {
        return $this->getData(self::VIEWER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setViewerId($viewerId)
    {
        return $this->setData(self::VIEWER_ID, $viewerId);
    }
}
