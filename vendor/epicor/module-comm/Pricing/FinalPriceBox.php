<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Pricing;

class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{

    /**
     * M2.3.3. going to cache final price
     *
     * @return bool|int|null
     */
    protected function getCacheLifetime()
    {
        return parent::hasCacheLifetime() ? parent::getCacheLifetime() : 0;
    }
}