<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\ReleaseNotification\Model\Data;

use Epicor\ReleaseNotification\Api\Data\LogInterface;
use Magento\Framework\DataObject;

class Log extends DataObject implements LogInterface
{
    /**
     * Columns names.
     */
    const ID = 'ID';
    const VIEWER_ID = 'viewer_id';
    const LAST_VIEW_VERSION = 'last_view_version';
    const HIDE_DATE = 'hide_date';

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

    /**
     * @inheritDoc
     */
    public function getLastViewVersion()
    {
        return $this->getData(self::LAST_VIEW_VERSION);
    }

    /**
     * @inheritDoc
     */
    public function setLastViewVersion($lastViewedVersion)
    {
        return $this->setData(self::LAST_VIEW_VERSION, $lastViewedVersion);
    }

    /**
     * @inheritDoc
     */
    public function getHideDate()
    {
        return $this->getData(self::HIDE_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setHideDate($date)
    {
        return $this->setData(self::HIDE_DATE, $date);
    }
}
