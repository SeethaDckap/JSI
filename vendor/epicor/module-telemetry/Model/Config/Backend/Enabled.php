<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\Config\Backend;

use Epicor\Telemetry\Model\ResourceModel\Notification;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

class Enabled extends Value
{
    /**
     * @var Notification
     */
    private $notificationResource;

    /**
     * Enabled constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param Notification $notificationResource
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        Notification $notificationResource,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->notificationResource = $notificationResource;

        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * After save.
     *
     * @return Enabled
     * @throws LocalizedException
     */
    public function afterSave()
    {
        if ($this->isValueChanged() && $this->getValue() == 1) {
            $this->flushNotifications();
        }

        return parent::afterSave();
    }

    /**
     * Flush notifications.
     *
     * @return void
     * @throws LocalizedException
     */
    public function flushNotifications()
    {
        $this->notificationResource->flushNotifications();
    }
}
