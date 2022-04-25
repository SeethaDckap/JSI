<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\QuickOrderPad\Model\Source;

/**
 * Class Message Types
 */
class SortDirections implements \Magento\Framework\Data\OptionSourceInterface
{
    const QOP_LIST_POSITION_ORDER = 0;
    const QOP_SORT_ASC = 1;
    const QOP_SORT_DESC = 2;
    
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['label' => 'List Position', 'value' => self::QOP_LIST_POSITION_ORDER],
            ['label' => 'Product Ascending', 'value' => self::QOP_SORT_ASC],
            ['label' => 'Product Descending', 'value' => self::QOP_SORT_DESC],
        ];
    }
}
