<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model;

use Magento\Framework\App\ObjectManager;
use Epicor\Telemetry\Model\Data\EventData;
use Epicor\Telemetry\Model\Data\PageViewData;

/**
 * Telemetry Client
 * @category   Epicor
 * @package    Epicor_Telemetry
 * @author     Epicor Websales Team
 *
 */
class Client
{
    /**
     * @var EventData
     */
    private $eventData;

    /**
     * @var PageViewData
     */
    private $pageViewData;

    /**
     * @var Context|mixed|null
     */
    private $context;

    /**
     * @var Channel|mixed|null
     */
    private $channel;

    /**
     * Client constructor.
     * @param EventData $eventData
     * @param PageViewData $pageViewData
     * @param ApplicationInsights $applicationInsights
     * @param Context|null $context
     * @param Channel|null $channel
     */
    public function __construct(
        EventData $eventData,
        PageViewData $pageViewData,
        ApplicationInsights $applicationInsights,
        Context $context = null,
        Channel $channel = null
    )
    {
        $this->eventData = $eventData;
        $this->pageViewData = $pageViewData;
        $this->context = $context ?: ObjectManager::getInstance()->get(Context::class);
        $this->channel = $channel ?: ObjectManager::getInstance()->get(Channel::class);
        $instrumentationKey = $applicationInsights->getInstrumentationKey();
        $this->context->setInstrumentationKey($instrumentationKey);
    }

    /**
     * Gets the Context object
     * @return Context|mixed|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Adding the custom event to queue
     * @param mixed $name
     * @param null $properties
     * @param null $measurements
     */
    public function trackEvent($name, $properties = null, $measurements = null)
    {
        $data = $this->eventData;
        $data->setName($name);
        if (is_null($properties) === false) {
            $data->setProperties($properties);
        }

        if (is_null($measurements) === false) {
            $data->setMeasurements($measurements);
        }

        $this->channel->addToQueue($data, $this->context);
    }

    /**
     * Sends an Page_View_Data to the Application Insights service.
     * @param mixed $name Friendly name of the page view.
     * @param mixed $url Url of the page view.
     * @param int duration Duration in milliseconds of the page view.
     * @param array $properties Array of name to value pairs. Use the name as the index and any string as the value.
     * @param array $measurements Array of name to double pairs. Use the name as the index and any double as the value.
     */
    public function trackPageView($name, $url, $duration = 0, $properties = null, $measurements = null)
    {
        $data = $this->pageViewData;
        $data->setName($name);
        $data->setUrl($url);
        $data->setDuration(Utils::convertMillisecondsToTimeSpan($duration));
        if ($properties !== null) {
            $data->setProperties($properties);
        }
        if ($measurements !== null) {
            $data->setMeasurements($measurements);
        }
        $this->channel->addToQueue($data, $this->context);
    }

    /**
     * Flushes the underlying Telemetry_Channel queue.
     * @param array $options - Guzzle client option overrides
     * @param bool  $sendAsync - Send the request asynchronously
     * @return \GuzzleHttp\Promise\PromiseInterface|null
     */
    public function flush($options = [], $sendAsync = false)
    {
        $response = $this->channel->send($options, $sendAsync);
        $this->channel->setQueue([]);
        return $response;
    }

}