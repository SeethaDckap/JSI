<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Config\Source;


class QuotesEnterVisibleOptions
{
    public function toOptionArray()
    {
        return array(
            array('value' => '1', 'label' => 'Quoted'),
            array('value' => '0', 'label' => 'All'),
        );
    }
}