<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Shipments\Details\Info\Renderer;

use Magento\Backend\Block\Context;
use Epicor\Comm\Helper\Messaging;
use Epicor\Customerconnect\Helper\TrackingUrl;

class Trackingnumber extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    /**
     * @var Messaging
     */
    private $messagingHelper;

    /**
     * @var TrackingUrl
     */
    private $trackingUrl;

    /**
     * TrackingNumber constructor.
     *
     * @param Context     $context
     * @param Messaging   $messagingHelper
     * @param TrackingUrl $trackingUrl
     * @param array       $data
     */
    public function __construct(
        Context $context,
        Messaging $messagingHelper,
        TrackingUrl $trackingUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->messagingHelper = $messagingHelper;
        $this->trackingUrl = $trackingUrl;
    }

    /**
     * Render Tracking Url.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = "";
        if (!is_null($row->getTrackingUrl())) {
            $html = $this->getLink(
                $row->getTrackingUrl(),
                $row->getTrackingNumber()
            );
        } else {
            $html .= $this->renderByTrackingNumber($row);
        }

        return $html;
    }

    /**
     * @param $row
     *
     * @return string
     */
    public function renderByTrackingNumber($row)
    {
        $html = "";
        $mappingUrl = null;
        $getGlobalUrl = $this->trackingUrl->getGlobalReturnUrl();
        if ($row->getTrackingNumber()) {
            if (!is_null($row->getMethodCode())) {
                $mappingMode
                    = $this->trackingUrl->getMappingShippingMethod($row->getMethodCode());
                $mappingUrl = $mappingMode->getTrackingUrl();
            }

            if ((!$getGlobalUrl) && (!$mappingUrl)) {
                $html .= $row->getTrackingNumber();
            }

            if (($getGlobalUrl) && (!$mappingUrl)) {
                $combineUrl = $this->trackingUrl->formatTrackingUrl(
                    $getGlobalUrl,
                    $row->getTrackingNumber()
                );
                $html .= $this->getLink($combineUrl, $row->getTrackingNumber());
            }

            if ($mappingUrl) {
                $combineUrl = $this->trackingUrl->formatTrackingUrl(
                    $mappingUrl,
                    $row->getTrackingNumber()
                );
                $html .= $this->getLink($combineUrl, $row->getTrackingNumber());
            }
        }

        return $html;
    }

    /**
     * @param string $url
     * @param string $title
     *
     * @return string
     */
    public function getLink($url, $title)
    {
        return '<a href="' . $url . '" target="_blank" >' . $title . '</a>';
    }


}
