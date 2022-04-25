<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Helper;

use Magento\Framework\App\Helper\Context;
use Epicor\Comm\Model\Erp\Mapping\ShippingmethodFactory;
use Epicor\Comm\Model\Erp\Mapping\Shippingmethod;

class TrackingUrl extends \Magento\Framework\App\Helper\AbstractHelper
{

    const SOU_DEFAULT_URL_CONFIG = "shipping/options/soudefaulturl";

    /**
     * @var ShippingmethodFactory
     */
    private $shippingmethodFactory;

    /**
     * TrackingUrl constructor.
     *
     * @param Context               $context
     * @param ShippingmethodFactory $shippingmethodFactory
     */
    public function __construct(
        Context $context,
        ShippingmethodFactory $shippingmethodFactory
    ) {
        parent::__construct($context);
        $this->shippingmethodFactory = $shippingmethodFactory;
    }

    /**
     * Replace template variables with the number
     *
     *
     * @return string
     */
    public function formatTrackingUrl($text, $trackNumber)
    {
        return str_replace('{{TNUM}}', $trackNumber, $text);
    }

    /**
     * Getting the Shipment code and Url.
     *
     * @param $shippingMethod
     *
     * @return null|Shippingmethod
     */
    public function getMappingShippingMethod($shippingMethod)
    {
        if (!$shippingMethod) {
            return null;
        }

        $model = $this->shippingmethodFactory->create();
        /* @var $model ShippingmethodFactory */
        return $model->loadMappingByStore($shippingMethod, 'erp_code');
    }

    /**
     * Return the global return URL.
     *
     * @return string
     */
    public function getGlobalReturnUrl()
    {
        return $this->scopeConfig->getValue(
            self::SOU_DEFAULT_URL_CONFIG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


}