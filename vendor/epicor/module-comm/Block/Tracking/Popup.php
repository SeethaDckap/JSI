<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Tracking;

use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Epicor\Customerconnect\Helper\TrackingUrl;

class Popup extends \Magento\Shipping\Block\Tracking\Popup
{
    private $updatedTrackingInfoData;

    /**
     * @var ShipmentTrackRepositoryInterface
     */
    private $shipmentTrackRepository;
    private $trackData;

    /**
     * @var TrackingUrl
     */
    private $trackingUrl;


    public function __construct(
        ShipmentTrackRepositoryInterface $shipmentTrackRepository,
        Context $context,
        Registry $registry,
        DateTimeFormatterInterface $dateTimeFormatter,
        TrackingUrl $trackingUrl,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateTimeFormatter, $data);
        $this->shipmentTrackRepository = $shipmentTrackRepository;
        $this->trackingUrl = $trackingUrl;
    }

    public function getUpdatedTrackingInfoData()
    {
        $trackingInfo = $this->getTrackingInfo();
        $this->updatedTrackingInfoData = [];
        $id = 0;
        foreach ($trackingInfo as $shipmentIncrementId => $tracking) {
            $this->trackData = null;
            $this->updatedTrackingInfoData[$shipmentIncrementId][] = $this->setTrackingData($tracking, $id);
            $id++;
        }

        return $this->updatedTrackingInfoData;
    }

    private function setTrackingData($tracking, $id)
    {
        $trackingId = $tracking[0]['track_id'] ?? null;
        return [
            'title' => $tracking[0]['title'] ?? '',
            'number' => $tracking[0]['number'] ?? '',
            'url' => $this->getTrackUrl($trackingId),
            'description' => $this->getDescription($trackingId),
            'carrier_code' => $this->getCarrierCode($trackingId)
        ];
    }

    private function getCurrentShippingInfo()
    {
        return $this->_registry->registry('current_shipping_info');
    }

    private function getTrackUrl($trackingId = null)
    {
        if ($trackingData = $this->getTrackingData($trackingId)) {
            return $trackingData->getUrl();
        }
    }

    private function getDescription($trackingId = null)
    {
        if ($trackingData = $this->getTrackingData($trackingId)) {
            return $trackingData->getDescription();
        }
    }

    /**
     * get Carrier Shipping Code.
     *
     * @param string $carrierCode
     *
     * @return mixed
     */
    private function getCarrierCode($carrierCode = null)
    {
        if ($trackingData = $this->getTrackingData($carrierCode)) {
            return $trackingData->getCarrierCode();
        }
    }

    private function getTrackingData($trackingId = null)
    {
        if (!$this->trackData) {
            $this->setTrackData($trackingId);
        }

        return $this->trackData;
    }

    private function setTrackData($trackingId = null)
    {
        if (!$trackingId) {
            $id = $this->getShipmentTrackingId();
        } else {
            $id = $trackingId;
        }
        if ($id) {
            $this->trackData =  $this->shipmentTrackRepository->get($id);
        }
    }

    private function getShipmentTrackingId()
    {
        /** @var \Magento\Shipping\Model\Info $shippingInfo */
        $shippingInfo = $this->getCurrentShippingInfo();
        if (!$id = $shippingInfo['ship_id'] ?? false) {
            $id = $shippingInfo['track_id'] ?? false;
        }

        return $id;
    }

    /**
     * get Global Return Url.
     *
     * @return string
     */
    public function getGlobalReturnUrl()
    {
        return $this->trackingUrl->getGlobalReturnUrl();
    }

    /**
     * get Mapping Shipping Method.
     *
     * @param string $methodCode
     *
     * @return \Epicor\Comm\Model\Erp\Mapping\Shippingmethod
     */
    public function getMappingShippingMethod($methodCode)
    {
        return $this->trackingUrl->getMappingShippingMethod($methodCode);
    }

    /**
     * Format Tracking Url.
     *
     * @param string $text
     * @param string $trackNumber
     *
     * @return string
     */
    public function formatTrackingUrl($text, $trackNumber)
    {
        return $this->trackingUrl->formatTrackingUrl($text, $trackNumber);
    }
}
