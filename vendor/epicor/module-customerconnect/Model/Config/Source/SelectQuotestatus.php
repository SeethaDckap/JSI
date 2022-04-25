<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Config\Source;

use \Epicor\Customerconnect\Model\Config\Source\Quotestatus;

/**
 * Quote Status dropwdown
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class SelectQuotestatus extends Quotestatus
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Quotestatus::QUOTE_STATUS_AWAITING,
                'label' => $this->getStatusDescription(Quotestatus::QUOTE_STATUS_AWAITING)
            ],
            [
                'value' => Quotestatus::QUOTE_STATUS_PENDING,
                'label' => $this->getStatusDescription(Quotestatus::QUOTE_STATUS_PENDING)
            ]
        ];
    }
}
