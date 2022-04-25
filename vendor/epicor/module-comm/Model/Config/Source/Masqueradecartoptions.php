<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Masqueradecartoptions
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'clear', 'label' => 'Clear'),
            array('value' => 'reprice', 'label' => 'Reprice'),
        );
    }

}
