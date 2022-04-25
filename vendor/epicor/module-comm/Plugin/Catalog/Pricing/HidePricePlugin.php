<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Catalog\Pricing;

class HidePricePlugin
{
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper
    )
    {
        $this->commHelper = $commHelper;
    }

    /**
     * Adds Decimal Validation
     *
     * @return array
     */
    public function afterRender(\Magento\Framework\Pricing\Render $subject, $html)
    {
        $hidePrice = $this->commHelper->getEccHidePrice();
        if (!$hidePrice || $hidePrice == 2) {
            return $html;
        } else {
            return '';
        }
    }
}
