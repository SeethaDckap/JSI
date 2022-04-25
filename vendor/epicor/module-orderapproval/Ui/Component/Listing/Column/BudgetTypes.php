<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_OrderApproval
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class BudgetTypes
 */
class BudgetTypes implements OptionSourceInterface
{
    const TYPE_DAILY     = 'Daily';
    const TYPE_MONTHLY   = 'Monthly';
    const TYPE_QUARTERLY = 'Quarterly';
    const TYPE_YEARLY    = 'Yearly';


    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::TYPE_DAILY,
                'label' => __('Daily'),
            ],
            [
                'value' => self::TYPE_MONTHLY,
                'label' => __('Monthly'),
            ],
            [
                'value' => self::TYPE_QUARTERLY,
                'label' => __('Quarterly'),
            ],
            [
                'value' => self::TYPE_YEARLY,
                'label' => __('Yearly'),
            ],
        ];
    }//end toOptionArray()


}//end class

