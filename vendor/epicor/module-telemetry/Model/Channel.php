<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model;

use GuzzleHttp\Client;
use Magento\Framework\App\ObjectManager;

/**
 * Class Channel
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class Channel
{
    /**
     * The endpoint URL to send data to.
     * @var string
     */
    private $endpointUrl;

    /**
     * The queue of already serialized JSON objects to send.
     * @var array
     */
    private $queue;

    /**
     * Client that is used to call out to the endpoint.
     * @var Client
     */
    private $client;

    /**
     * If true, then the data will be gzipped before sending to application insights.
     * @var false
     */
    private $sendGzipped;

    /**
     * Channel constructor.
     * @param string $endpointUrl Optional. Allows caller to override which endpoint to send data to.
     * @param null $client Guzzle client if it exists
     */
    public function __construct(
        $endpointUrl = 'https://dc.services.visualstudio.com/v2/track',
        $client = null
    )
    {
        $this->endpointUrl = $endpointUrl;
        $this->queue = [];
        $this->client = $client;
        $this->sendGzipped = false;

        if ($client === null) {
            $this->client = new Client();
        }
    }

    /**
     * Returns the current URL this TelemetrySender will send to.
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->endpointUrl;
    }

    /**
     * Sets the current URL this TelemetrySender will send to.
     * @param string $endpointUrl
     */
    public function setEndpointUrl($endpointUrl)
    {
        $this->endpointUrl = $endpointUrl;
    }

    /**
     * Returns the current queue.
     * @return array
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Sets the current queue.
     * @param array $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Summary of getSerializedQueue
     * @return string JSON representation of queue.
     */
    public function getSerializedQueue()
    {
        $queueToEncode = [];
        foreach ($this->queue as $dataItem) {
            array_push($queueToEncode, Utils::getUnderlyingData($dataItem->jsonSerialize()));
        }
        return json_encode($queueToEncode);
    }

    /**
     * @return bool
     */
    public function getSendGzipped()
    {
        return $this->sendGzipped;
    }

    /**
     * @param bool $sendGzipped
     */
    public function setSendGzipped($sendGzipped)
    {
        $this->sendGzipped = $sendGzipped;
    }


    /**
     * Writes the item into the sending queue for subsequent processing.
     * @param array $data The telemetry item to send.
     * @param Context $telemetryContext The context to use.
     * @param null $startTime
     */
    public function addToQueue($data, Context $telemetryContext, $startTime = null)
    {
        // If no data or context provided, we just return to not cause upstream issues as a result of telemetry.
        if ($data === null || $telemetryContext === null) {
            return;
        }

        $envelope = ObjectManager::getInstance()->get(Envelope::class);

        // Main envelope properties.
        $envelope->setName($data->getEnvelopeTypeName());
        if ($startTime === null) {
            $startTime = $data->getTime();
        }

        $envelope->setTime(Utils::returnISOStringForTime($startTime));

        // The instrumentation key to use.
        $envelope->setInstrumentationKey($telemetryContext->getInstrumentationKey());

        // Copy all context into the Tags array.
        $envelope->setTags(array_merge(
            $telemetryContext->getApplicationContext()->jsonSerialize(),
            $telemetryContext->getDeviceContext()->jsonSerialize(),
            $telemetryContext->getCloudContext()->jsonSerialize(),
            $telemetryContext->getLocationContext()->jsonSerialize(),
            $telemetryContext->getOperationContext()->jsonSerialize(),
            $telemetryContext->getSessionContext()->jsonSerialize(),
            $telemetryContext->getUserContext()->jsonSerialize(),
            $telemetryContext->getInternalContext()->jsonSerialize()
            )
        );
        // Merge properties from global context to local context.
        $contextProperties = $telemetryContext->getProperties();
        if (method_exists($data, 'getProperties') === true
            && $contextProperties !== null
            && count($contextProperties) > 0
        ) {
            $dataProperties = $data->getProperties();
            if ($dataProperties === null) {
                $dataProperties = [];
            }
            foreach ($contextProperties as $key => $value) {
                if (array_key_exists($key, $dataProperties) === false) {
                    $dataProperties[$key] = $value;
                }
            }
            $data->setProperties($dataProperties);
        }

        // Embed the main data object.
        $envelope->setData(new Data());
        $envelope->getData()->setBaseType($data->getDataTypeName());
        $envelope->getData()->setBaseData($data);
        array_push($this->queue, $envelope);
        return;
    }

    /**
     * Summary of send
     * @param array $options
     * @param bool  $sendAsync
     * @return \GuzzleHttp\Promise\PromiseInterface|\Psr\Http\Message\ResponseInterface|null
     */
    public function send($options = [], $sendAsync = false)
    {
        $response = null;
        $useGuzzle = $this->client !== null;

        if (count($this->queue) === 0) {
            return;
        }

        $serializedTelemetryItem = $this->getSerializedQueue();

        if($this->sendGzipped && $useGuzzle) {
            $headersArray = [
                'Content-Encoding' => 'gzip',
            ];
            $body = gzencode($serializedTelemetryItem);
        } else {
            $headersArray = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8'
            ];
            $body = utf8_encode($serializedTelemetryItem);
        }

        if ($useGuzzle) {
            $options = array_merge(
                [
                    'headers' => $headersArray,
                    'body' => $body,
                    'verify' => false
                ],
                $options
            );
            if ($sendAsync && method_exists($this->client, 'sendAsync')) {
                $response = $this->client->postAsync($this->endpointUrl, $options);
            } else {
                $response = $this->client->post($this->endpointUrl, $options);
            }
        }
        return $response;
    }
}