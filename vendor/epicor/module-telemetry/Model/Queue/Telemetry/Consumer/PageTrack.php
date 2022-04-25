<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Telemetry\Model\Queue\Telemetry\Consumer;

use Epicor\Telemetry\Model\Client;
use Epicor\Telemetry\Api\Data\Telemetry\PageTrackInterface;
use Psr\Log\LoggerInterface;

/**
 * Consumer Queue For Telemetry Page track Use For Sync.
 * Call Via Magento Queue Mechanism.
 * @category   Epicor
 * @package    Epicor_Telemetry
 * @author     Epicor Websales Team
 */
class PageTrack
{

    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Client
     */
    private $telemetryClient;

    /**
     * Consumer constructor.
     * @param LoggerInterface $logger LoggerInterface.
     * @param Client $telemetryClient
     */
    public function __construct(
        LoggerInterface $logger,
        Client $telemetryClient
    ) {
        $this->logger            = $logger;
        $this->telemetryClient   = $telemetryClient;
    }

    /**
     * Consumer Logic. Pushes the data to application insights
     * @param PageTrackInterface $pageTrack PageTrackInterface.
     * @return void
     */
    public function process(PageTrackInterface $pageTrack)
    {
        try {
            $eventName = $pageTrack->getEventName();
            if ($eventName) {
                $trackData = [
                    "Shipping Code" => $pageTrack->getShippingCode(),
                    "Shipping Title" => $pageTrack->getShippingTitle(),
                    "Payment Code" => $pageTrack->getPaymentCode(),
                    "Payment Title" => $pageTrack->getPaymentTitle()
                ];
                $this->telemetryClient->trackEvent($eventName, $trackData);
                $this->telemetryClient->flush();
            }
        } catch (\Exception $e) {
            $this->logger->debug($e);
        }
        return;
    }

}
