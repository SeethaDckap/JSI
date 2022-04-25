<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Ui\DataProviders\Product\Related;

use Magento\Catalog\Ui\DataProvider\Product\Related\AbstractDataProvider;

/**
 * Class SubstituteDataProvider
 * @package Epicor\Comm\Ui\DataProviders\Product\Related
 */
class SubstituteDataProvider extends AbstractDataProvider
{
    /**
     * @return string
     */
    protected function getLinkType()
    {
        return 'substitute';
    }
}
