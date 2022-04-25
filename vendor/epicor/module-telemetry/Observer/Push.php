<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Telemetry\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Epicor\Telemetry\Model\ApplicationInsights;
use Epicor\Telemetry\Api\Data\Telemetry\PageTrackInterface;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Class SalesQuoteSaveAfterObserver
 * @category   Epicor
 * @package    Epicor_Telemetry
 * @author     Epicor Websales Team
 */
class Push implements ObserverInterface
{
    /**
     * @var mixed|null
     */
    private $instrumentationKey;

    /**
     * @var PageTrackInterface
     */
    private $pageTrack;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * Push constructor.
     * @param ApplicationInsights $applicationInsights
     * @param PageTrackInterface $pageTrack
     * @param PublisherInterface $publisher
     */
    public function __construct(
        ApplicationInsights $applicationInsights,
        PageTrackInterface $pageTrack,
        PublisherInterface $publisher
    )
    {
        $this->instrumentationKey = $applicationInsights->getInstrumentationKey();
        $this->pageTrack = $pageTrack;
        $this->publisher = $publisher;
    }

    /**
     * Telemetry server push of Shipment and Payment info
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->instrumentationKey === null) return;
        $order = $observer->getOrder();
        $payment = $order->getPayment()->getMethodInstance();
        $paymentTitle = $payment->getTitle();
        $paymentCode = $payment->getCode();
        $shippingCode = $order->getShippingMethod();
        $shippingDescription = $order->getShippingDescription();

        $this->pageTrack->setEventName("Order");
        $this->pageTrack->setShippingCode($shippingCode);
        $this->pageTrack->setShippingTitle($shippingDescription);
        $this->pageTrack->setPaymentCode($paymentCode);
        $this->pageTrack->setPaymentTitle($paymentTitle);
        $this->publisher->publish('ecc.telemetry.pagetrack', $this->pageTrack);
        return;
    }
}