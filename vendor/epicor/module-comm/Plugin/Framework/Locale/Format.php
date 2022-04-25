<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Framework\Locale;

use \Epicor\Comm\Helper\Data as  CommDataHelper;
use \Magento\Framework\Locale\Format as LocaleFormat;

class Format
{
    /**
     * @var CommDataHelper
     */
    private $commDataHelper;

    /**
     * @var int
     */
    private $pricePrecision;

    /**
     * Format constructor.
     * @param CommDataHelper $commDataHelper
     */
    public function __construct(
        CommDataHelper $commDataHelper
    )
    {
        $this->commDataHelper = $commDataHelper;
        $this->pricePrecision = $this->commDataHelper->getProductPricePrecision();
    }

    /**
     * @param LocaleFormat $subject
     * @param $result
     * @return mixed
     */
    public function afterGetPriceFormat(
        LocaleFormat $subject,
        $result
    )
    {
        $result['precision'] = $this->pricePrecision;
        $result['requiredPrecision'] = $this->pricePrecision;
        return $result;
    }
}
