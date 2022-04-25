<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Acl\AclResource;

use Magento\Framework\App\ObjectManager;
use Epicor\Comm\Model\Serialize\Serializer\Json;

class Provider implements ProviderInterface
{
    /**
     * Cache key for ACL roles cache
     */
    const ACL_RESOURCES_CACHE_KEY = 'provider_access_resources_cache';
    /**
     * Cache key for ACL roles cache
     */
    const ALL_RESOURCES_CACHE_KEY = 'provider_access_all_resources_cache';

    /**
     * @var \Magento\Framework\Config\ReaderInterface
     */
    protected $_configReader;

    /**
     * @var TreeBuilder
     */
    protected $_resourceTreeBuilder;

    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface
     */
    private $aclDataCache;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var string
     */
    private $allcacheKey;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    protected $cmsCollectionFactory;

    /**
     * @var \Magento\Widget\Model\ResourceModel\Widget\Instance\Collection
     */
    protected $widgetcollectionFactory;

    /**
     * @param \Magento\Framework\Config\ReaderInterface $configReader
     * @param TreeBuilder $resourceTreeBuilder
     * @param \Magento\Framework\Acl\Data\CacheInterface $aclDataCache
     * @param Json $serializer
     * @param string $cacheKey
     */
    public function __construct(
        \Magento\Framework\Config\ReaderInterface $configReader,
        TreeBuilder $resourceTreeBuilder,
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $cmscollectionFactory,
        \Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory $widgetcollectionFactory,
        \Magento\Framework\Acl\Data\CacheInterface $aclDataCache = null,
        Json $serializer = null,
        $cacheKey = self::ACL_RESOURCES_CACHE_KEY,
        $allcacheKey = self::ALL_RESOURCES_CACHE_KEY
    )
    {
        $this->_configReader = $configReader;
        $this->_resourceTreeBuilder = $resourceTreeBuilder;
        $this->aclDataCache = $aclDataCache ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Config\CacheInterface::class
        );
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->cacheKey = $cacheKey;
        $this->allcacheKey = $allcacheKey;
        $this->cmscollectionFactory = $cmscollectionFactory;
        $this->widgetcollectionFactory = $widgetcollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCmsResources($tree)
    {
        $collection = $this->cmscollectionFactory->create();
        //$collection->addFieldToFilter('is_active' , \Magento\Cms\Model\Page::STATUS_ENABLED);
        if($collection->getSize()) {
            $lastnode = count($tree[0]['children']);
            $tree[0]['children'][$lastnode] = [
                'id' => 'Epicor_CMS::cms',
                'title' => 'CMS Pages',
                'actioncontoler' => true,
                'sortOrder' => 100
            ];

            foreach ($collection as $cms) {
                $tree[0]['children'][$lastnode]['children'][] = [
                    'id' => 'Epicor_CMS::cms_' . $cms->getId(),
                    'title' => $cms->getTitle(),
                ];
            }
        }

        return $tree;

    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetResources($tree)
    {
        $collection = $this->widgetcollectionFactory->create();
        //$collection->addFieldToFilter('is_active' , \Magento\Cms\Model\Page::STATUS_ENABLED);
        if($collection->getSize()) {
            $lastnode = count($tree[0]['children']);
            $tree[0]['children'][$lastnode] = [
                'id' => 'Epicor_Widget::widget',
                'title' => 'Widget',
                'actioncontoler' => true,
                'sortOrder' => 100
            ];

            foreach ($collection as $widget) {
                $tree[0]['children'][$lastnode]['children'][] = [
                    'id' => 'Epicor_Widget::widget_' . $widget->getId(),
                    'title' => $widget->getTitle(),
                ];
            }
        }

        return $tree;

    }

    /**
     * {@inheritdoc}
     */
    public function getAclResources()
    {
        $tree = $this->aclDataCache->load($this->cacheKey);
        if ($tree) {
            $tree = $this->serializer->unserialize($tree);
            $tree = $this->getCmsResources($tree);
           // $tree = $this->getWidgetResources($tree);
            return $tree;
        }
        $aclResourceConfig = $this->_configReader->read();
        if (!empty($aclResourceConfig['config']['acl']['resources'])) {
            $tree = $this->_resourceTreeBuilder->build($aclResourceConfig['config']['acl']['resources']);
            $this->aclDataCache->save($this->serializer->serialize($tree), $this->cacheKey);
            $tree = $this->getCmsResources($tree);
            return $tree;
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllResources()
    {
        $alltree = $this->aclDataCache->load($this->allcacheKey);
        if ($alltree) {
            return $this->serializer->unserialize($alltree);
        }
        $aclResourceConfig = $this->_configReader->read();
        if (!empty($aclResourceConfig['config']['acl']['resources'])) {
            $alltree = $this->_resourceTreeBuilder->getAllResource($aclResourceConfig['config']['acl']['resources']);
            $this->aclDataCache->save($this->serializer->serialize($alltree), $this->allcacheKey);
            return $alltree;
        }
        return [];
    }
}
