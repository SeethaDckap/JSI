<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Helper\Product;

use Epicor\Comm\Helper\Data as CommHelper;
use Magento\Catalog\Helper\Product\Configuration;

class ConfigurationPlugin
{
    /**
     * @var CommHelper
     */
    private $commHelper;

    /**
     * ConfigurationPlugin constructor.
     * @param CommHelper $commHelper
     */
    public function __construct(
        CommHelper $commHelper
    ) {
        $this->commHelper = $commHelper;
    }

    /**
     * @param Configuration $subject
     * @param string $result
     * @return string
     */
    public function afterGetFormattedOptionValue(Configuration $subject, $result)//NOSONAR
    {
        if ($this->commHelper->isPriceDisplayDisabled()) {
            $pattern = '/<span\sclass="price">.*<\/span>/';
            return preg_replace($pattern, '', $result);
        }

        return $result;
    }
}