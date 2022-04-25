<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Shipment\Email;

use Magento\Framework\View\Element\Template\Context;
use Epicor\Customerconnect\Helper\TrackingUrl;

/**
 * Store Switcher
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Track extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var TrackingUrl
     */
    private $trackingUrl;

    /**
     * Track constructor.
     *
     * @param Context     $context
     * @param TrackingUrl $trackingUrl
     * @param array       $data
     */
    public function __construct(
        Context $context,
        TrackingUrl $trackingUrl,
        array $data = []
    ) {
        $this->logger = $context->getLogger();
        $this->scopeConfig = $context->getScopeConfig();
        $this->trackingUrl = $trackingUrl;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Tracking URL for Shipping Email.
     * @param $tracking
     *
     * @return string
     */
    public function getTrackingUrl($tracking)
    {
        $html = "";
        $mappingUrl = null;
        $getGlobalUrl = $this->trackingUrl->getGlobalReturnUrl();
        if ($tracking->getNumber()) {

            if ($tracking->getUrl()) {
                return $this->getUrlLink(
                    $tracking->getNumber(),
                    $tracking->getUrl()
                );
            }

            if (!is_null($tracking->getCarrierCode())) {
                $mappingMode = $this->trackingUrl->getMappingShippingMethod(
                    $tracking->getCarrierCode()
                );
                $mappingUrl = $mappingMode->getTrackingUrl();
            }

            if ((!$getGlobalUrl) && (!$mappingUrl)) {
                $html = $this->escapeHtml($tracking->getNumber());
            }

            if (($getGlobalUrl) && (!$mappingUrl)) {
                $combineUrl = $this->trackingUrl->formatTrackingUrl(
                    $getGlobalUrl,
                    $tracking->getNumber()
                );

                $html = $this->getUrlLink($tracking->getNumber(), $combineUrl);
            }

            if ($mappingUrl) {
                $combineUrl = $this->trackingUrl->formatTrackingUrl(
                    $mappingUrl,
                    $tracking->getNumber()
                );
                $html = $this->getUrlLink($tracking->getNumber(), $combineUrl);
            }
        } elseif ($tracking->getUrl()) {
            $html = $this->getUrlLink($tracking->getUrl(), $tracking->getUrl());
        }

        return $html;

    }

    /**
     * get url link.
     *
     * @param $number
     * @param $link
     *
     * @return string
     */
    public function getUrlLink($number, $link)
    {
        return '<a href="'.$this->escapeHtml($link)
            .'" target="_blank" >'
            .$this->escapeHtml($number).'</a>';
    }

}
