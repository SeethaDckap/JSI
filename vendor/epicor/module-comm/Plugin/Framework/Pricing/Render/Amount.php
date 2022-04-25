<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Framework\Pricing\Render;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use \Magento\Framework\Pricing\Render\Amount as PricingAmount;
use \Epicor\Comm\Helper\Data as  CommDataHelper;

class Amount
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
     * PriceCurrency constructor.
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
     * @param PricingAmount $subject
     * @param $amount
     * @param bool $includeContainer
     * @param int $precision
     * @return array
     */
    public function beforeFormatCurrency(
        PricingAmount $subject,
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION
    ) {
        return [$amount, $includeContainer, $this->pricePrecision];
    }
}
