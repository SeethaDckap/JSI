<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Framework\Pricing;

use \Magento\Framework\Pricing\PriceCurrencyInterface;
use \Epicor\Comm\Helper\Data as  CommDataHelper;

class PriceCurrency
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
     * @param PriceCurrencyInterface $subject
     * @param $amount
     * @param bool $includeContainer
     * @param $precision
     * @param null $scope
     * @param null $currency
     * @return array
     */
    public function beforeFormat(
        PriceCurrencyInterface $subject,
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        return [$amount, $includeContainer, $this->pricePrecision, $scope, $currency];
    }

    /**
     * Setting Price Precision as per the store configuration
     *
     * @param PriceCurrencyInterface $subject
     * @param $amount
     * @param null $scope
     * @param null $currency
     * @param int $precision
     * @return array
     */
    public function beforeConvertAndRound(
        PriceCurrencyInterface $subject,
        $amount,
        $scope = null,
        $currency = null,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION
    )
    {
        return [$amount, $scope, $currency, $this->pricePrecision];
    }

    /**
     * @param PriceCurrencyInterface $subject
     * @param $result
     * @param $price
     * @return false|float
     */
    public function afterRound(
        PriceCurrencyInterface $subject,
        $result,
        $price
    )
    {
        return round($price, $this->pricePrecision);
    }
}
