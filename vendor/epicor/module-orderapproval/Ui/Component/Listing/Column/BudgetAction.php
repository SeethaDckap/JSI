<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_OrderApproval
 * @subpackage Ui
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class BudgetAction
 */
class BudgetAction implements OptionSourceInterface
{
    const STOP_CHECKOUT = '0';
    const SUSPEND       = '1';


    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::STOP_CHECKOUT,
                'label' => __('Do Not Allow Checkout If Over Budget'),
            ],
            [
                'value' => self::SUSPEND,
                'label' => __('Suspend Order If Over Budget'),
            ],
        ];
    }//end toOptionArray()


}//end class

