<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Config\Source;


class Roundingtypes
{

    public function toOptionArray()
    {
        return array(
            array('value' => PHP_ROUND_HALF_UP, 'label' => "Round Up"),
            array('value' => PHP_ROUND_HALF_DOWN, 'label' => "Round Down"),
            array('value' => PHP_ROUND_HALF_EVEN, 'label' => "Round Even"),
            array('value' => PHP_ROUND_HALF_ODD, 'label' => "Round Odd"),
        );
    }

}
