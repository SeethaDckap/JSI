<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Transactionlogs;


/**
 * Transaction Log Types
 */
class LogTypes implements \Magento\Framework\Option\ArrayInterface
{


    /**
     * toOptionArray
     *
     * @return array $result
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $result;
    }

    /**
     * Get Options
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            'PunchOut Setup Request' => __('PunchOut Setup Request'),
            'PunchOut Order' => __('PunchOut Order'),
            'Purchase Order' => __('Purchase Order')
        ];
    }

}
