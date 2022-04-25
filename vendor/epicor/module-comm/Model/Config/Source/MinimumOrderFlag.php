<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;


class MinimumOrderFlag implements ArrayInterface
{
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => 'Global Default'],['value' => 1, 'label'=> 'ERP Account']];
    }
}