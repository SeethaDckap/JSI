<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\ReleaseNotification\Ui\DataProvider\Modifier;

use Magento\ReleaseNotification\Model\ContentProviderInterface;
use Magento\ReleaseNotification\Ui\Renderer\NotificationRenderer;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Backend\Model\Auth\Session;
use Psr\Log\LoggerInterface;

/**
 * Modifies the metadata returning to the Release Notification data provider
 */
class MagentoNotifications implements ModifierInterface
{
    /**
     * @var ContentProviderInterface
     */
    private $contentProvider;

    /**
     * @var NotificationRenderer
     */
    private $renderer;

    /**
     * Prefix for cache
     *
     * @var string
     */
    private static $cachePrefix = 'release-notification-content-';

    /**
     * @var CacheInterface
     */
    private $cacheStorage;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ContentProviderInterface $contentProvider
     * @param NotificationRenderer $render
     * @param CacheInterface $cacheStorage
     * @param SerializerInterface $serializer
     * @param ProductMetadataInterface $productMetadata
     * @param Session $session
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContentProviderInterface $contentProvider,
        NotificationRenderer $render,
        CacheInterface $cacheStorage,
        SerializerInterface $serializer,
        ProductMetadataInterface $productMetadata,
        Session $session,
        LoggerInterface $logger
    ) {
        $this->contentProvider = $contentProvider;
        $this->renderer = $render;
        $this->cacheStorage = $cacheStorage;
        $this->serializer = $serializer;
        $this->productMetadata = $productMetadata;
        $this->session = $session;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $modalContent = $this->getNotificationContent();

        if (empty($modalContent)) {
            return $meta;
        }

        if (!empty($meta['pages'])) {
            $count = count($meta['pages']);
            foreach ($modalContent['pages'] as $page) {
                $page['name'] = $count+1;
                $page['id'] = $count+1;
                $meta['pages'][] = $page;
                $count++;
            }
        } else {
            $meta['pages'] = $modalContent['pages'];
        }

        return $meta;
    }

    /**
     * Returns the notification modal content data
     *
     * @returns array|false
     */
    private function getNotificationContent()
    {
        $version = strtolower($this->getTargetVersion());
        $edition = strtolower($this->productMetadata->getEdition());
        $locale = strtolower($this->session->getUser()->getInterfaceLocale());

        $cacheKey = self::$cachePrefix . $version . "-" . $edition . "-" . $locale;
        $modalContent = $this->cacheStorage->load($cacheKey);
        if (empty($modalContent)) {
            $modalContent = $this->contentProvider->getContent($version, $edition, $locale);
            $this->cacheStorage->save($modalContent, $cacheKey);
        }

        return !$modalContent ? $modalContent : $this->unserializeContent($modalContent);
    }

    /**
     * Unserializes the notification modal content to be used for rendering
     *
     * @param string $modalContent
     * @return array|false
     */
    private function unserializeContent($modalContent)
    {
        $result = false;

        try {
            $result = $this->serializer->unserialize($modalContent);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning(
                sprintf(
                    'Failed to unserialize the release notification content. The error is: %s',
                    $e->getMessage()
                )
            );
        }

        return $result;
    }

    /**
     * Returns the current Magento version used to retrieve the release notification content.
     * Version information after the dash (-) character is removed (ex. -dev or -rc).
     *
     * @return string
     */
    private function getTargetVersion()
    {
        $metadataVersion = $this->productMetadata->getVersion();
        $version = strstr($metadataVersion, '-', true);

        return !$version ? $metadataVersion : $version;
    }
}

