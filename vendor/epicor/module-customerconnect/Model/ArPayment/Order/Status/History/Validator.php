<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\Order\Status\History;

use Epicor\Customerconnect\Model\ArPayment\Order\Status\History;

/**
 * Class Validator
 * @package Epicor\Customerconnect\Model\ArPayment\Order\Status\History
 */
class Validator
{
    /**
     * @var array
     */
    protected $requiredFields = ['parent_id' => 'Order Id'];

    /**
     * @param History $history
     * @return array
     */
    public function validate(History $history)
    {
        $warnings = [];
        foreach ($this->requiredFields as $code => $label) {
            if (!$history->hasData($code)) {
                $warnings[] = sprintf('%s is a required field', $label);
            }
        }
        return $warnings;
    }
}
