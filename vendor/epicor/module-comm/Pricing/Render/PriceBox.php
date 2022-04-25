<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Pricing\Render;

class PriceBox extends \Magento\Catalog\Pricing\Render\PriceBox
{
    /**
     * M2.3.3. going to cache final price
     *
     * @return bool|int|null
     */
    public function getCacheLifetime()
    {
        return parent::hasCacheLifetime() ? parent::getCacheLifetime() : 0;
    }
}
