<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Ui\Component\Boost\Form;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Registry;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Epicor\Elasticsearch\Api\Data\BoostInterface;
use Epicor\Elasticsearch\Model\Boost;
use Epicor\Elasticsearch\Model\ResourceModel\Boost\CollectionFactory as BoostCollectionFactory;

/**
 * Boost Data provider for adminhtml edit form
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var PoolInterface
     */
    private $modifierPool;

    /**
     * DataProvider constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param BoostCollectionFactory $boostCollectionFactory
     * @param PoolInterface $modifierPool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        BoostCollectionFactory $boostCollectionFactory,
        PoolInterface $modifierPool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection   = $boostCollectionFactory->create();
        $this->modifierPool = $modifierPool;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        foreach ($this->getCollection()->getItems() as $itemId => $item) {
            $item->getResource()->afterLoad($item);
            $this->data[$itemId] = $item->toArray();
        }
        /** @var \Magento\Ui\DataProvider\Modifier\ModifierInterface $modifier */
        foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $this->meta = parent::getMeta();
        /** @var \Magento\Ui\DataProvider\Modifier\ModifierInterface $modifier */
        foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
            $this->meta = $modifier->modifyMeta($this->meta);
        }
        return $this->meta;
    }
}
