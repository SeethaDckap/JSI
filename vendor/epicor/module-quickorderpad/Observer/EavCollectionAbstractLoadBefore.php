<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Observer;

use Magento\Framework\Event\ObserverInterface;
use Epicor\QuickOrderPad\Model\Source\PositionSort;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as SearchCollectionClass;
use Magento\Framework\Event\Observer;

class EavCollectionAbstractLoadBefore implements ObserverInterface
{
    const MAX_PAGE_SIZE = 9999;
    /**
     * @var PositionSort
     */
    private $positionSort;

    public function __construct(
        PositionSort $positionSort
    ){
        $this->positionSort = $positionSort;
    }

    /**
     * Set page size to a max value in order to request search engine
     * to return all values in 1 request
     * @param Observer $observer
     * @return bool|void
     */
    public function execute(Observer $observer)
    {
        $collection = $observer->getCollection();
        if (!$collection instanceof SearchCollectionClass) {
            return false;
        }

        if ($this->positionSort->isQopCatalogSearch($collection)) {
            $collection->setPageSize(self::MAX_PAGE_SIZE);
            $collection->setCurPage(1);
        }
    }
}
