<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Plugin\Model\Entity\Collection;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Epicor\QuickOrderPad\Model\Source\PositionSort;

class AbstractCollectionPlugin
{
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
     * @param AbstractCollection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     */
    public function before_loadEntities(AbstractCollection $subject, $printQuery = false, $logQuery = false)
    {
        if ($this->positionSort->isQopCatalogSearch($subject)) {
            $this->positionSort->setOrderForSearchQuery($subject);
        }
    }
}
