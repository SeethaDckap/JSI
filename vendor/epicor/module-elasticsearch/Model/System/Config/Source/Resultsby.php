<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\System\Config\Source;

/**
 * Display Product Search Results by Source
 */
class Resultsby extends \Magento\Framework\App\Config\Value
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Name')],
            ['value' => 2, 'label' => __('Short Description')]
        ];
    }
}
