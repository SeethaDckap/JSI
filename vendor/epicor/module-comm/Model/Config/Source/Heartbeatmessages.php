<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Heartbeatmessages
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'hrt', 'label' => "HRT - Heart Beat"),
            array('value' => 'ast', 'label' => "AST - Account Status"),
        );
    }

}
