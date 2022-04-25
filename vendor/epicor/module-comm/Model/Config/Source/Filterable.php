<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Filterable
{
    public function toArray()
    {
        return [0 => __('No')
               ,1 =>  __('Filterable (with results)')
               ,2 => __('Filterable (no results)')
        ];
    }

}
