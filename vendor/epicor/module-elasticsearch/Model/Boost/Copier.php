<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\Boost;

use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Epicor\Elasticsearch\Api\Data\BoostInterface;
use Epicor\Elasticsearch\Api\Data\BoostInterfaceFactory;
use Epicor\Elasticsearch\Model\Boost;

/**
 * Boost copier.
 */
class Copier
{
    /**
     * Boost Factory
     *
     * @var BoostInterfaceFactory
     */
    protected $boostFactory;

    /**
     * @var PoolInterface
     */
    private $modifierPool;

    /**
     * @param BoostInterfaceFactory $boostFactory
     * @param PoolInterface $modifierPool
     */
    public function __construct(
        BoostInterfaceFactory $boostFactory,
        PoolInterface $modifierPool
    ) {
        $this->boostFactory = $boostFactory;
        $this->modifierPool = $modifierPool;
    }

    /**
     * Create boost duplicate
     *
     * @param BoostInterface $boost
     * @return BoostInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function copy(BoostInterface $boost): BoostInterface
    {
        $boostData = [$boost->getId() => $boost->getData()];
        foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
            $boostData = $modifier->modifyData($boostData);
        }
        $boostData = array_shift($boostData);
        /** @var Boost $duplicate */
        $duplicate = $this->boostFactory->create();
        $duplicate->setData($boostData);
        //$duplicate->setIsActive(0);
        $duplicate->setFromDate(\DateTime::createFromFormat('Y-m-d', $boostData['from_date'])->format('m/d/Y'));
        $duplicate->setToDate(\DateTime::createFromFormat('Y-m-d', $boostData['to_date'])->format('m/d/Y'));
        $duplicate->setId(null);
        return $duplicate;
    }
}
