<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elasticsearch\Model\Config\Source;

/**
 * Class Currencyfields
 * @package Epicor\Elasticsearch\Model\Config\Source
 */
class Showon
{
    /**
     * Gets the Recent Searches Show On options
     * @return \string[][]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'first_click', 'label' => "Quick Search - First Click"],
            ['value' => 'auto_suggest', 'label' => "Quick Search - Auto Suggest"]
        ];
    }

}
