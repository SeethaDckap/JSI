<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Temando\Shipping;

use Psr\Log\LogLevel;

class Carrier 
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 100.1.0
     */
    protected $productMetadata;
    
    /**
     *
     * @var \Magento\Framework\Module\Manager 
     */
    protected $moduleManager;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
        
     public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\Manager $moduleManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->productMetadata = $productMetadata;
        $this->moduleManager = $moduleManager;
        $this->logger = $logger;
    }
    
    /**
     * Returns Temando Shipping Methods
     *
     * @return array
     */
    public function afterGetAllowedMethods(
        \Temando\Shipping\Model\Shipping\Carrier $subject,
        array  $output
    )
    {
        if($this->moduleManager->isEnabled('Temando_Shipping')){
            $output = $this->getTemandoShippingMethods();
        }
        return $output;
    }
    
    /**
     * Gets Temando Shipping Experiences via API
     * 
     * This method is developed to work in all version of magento as Temando Shipping module is only above Magento version 2.2.0
     * This method needs to be rechecked once Magento 2.1.7 is not used
     * 
     * @return array
     * @throws RestClientErrorException
     */
    public function getTemandoShippingMethods()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $objectManager->get('Temando\Shipping\Model\Config\ModuleConfigInterface');
        $_methods = [];
        if ($config->isEnabled()) {
            $auth = $objectManager->get('Temando\Shipping\Rest\AuthenticationInterface');
            $wsconfig = $objectManager->get('Temando\Shipping\Webservice\Config\WsConfigInterface');
            $restClient = $objectManager->get('Temando\Shipping\Rest\RestClientInterface');
            $responseParser = $objectManager->get('Temando\Shipping\Rest\SchemaMapper\ParserInterface');
            $endpoint = $wsconfig->getApiEndpoint();
            $accountId = $wsconfig->getAccountId();
            $apiVersion = $wsconfig->getApiVersion();
            $bearerToken = $wsconfig->getBearerToken();
            $uri = sprintf('%s/experiences', $endpoint);
            $queryParams = [];
            $this->logger->log(LogLevel::DEBUG, sprintf('%s?%s', $uri, http_build_query($queryParams)));
            try {
                $auth->connect($accountId, $bearerToken);
                $headers = [
                            'Cache-Control' => 'no-cache',
                            'Content-Type'  => 'application/vnd.api+json',
                            'Accept'        => 'application/vnd.api+json',
                            'Origin'        => $endpoint,
                            'Version'       => $apiVersion,
                            'Authorization' => sprintf('Bearer %s', $auth->getSessionToken()),
                        ];
                $rawResponse = $restClient->get($uri, $queryParams, $headers);
                $this->logger->log(LogLevel::DEBUG, $rawResponse);
                $experiences = json_decode($rawResponse, true);
                if (isset($experiences['data'])) {
                    foreach ($experiences['data'] as $method) {
                        $_method = isset($method['attributes']['ExperienceId']) ? $method['attributes']['ExperienceId'] : '';
                        $_methodLabel = isset($method['attributes']['ExperienceName']) ? $method['attributes']['ExperienceName'] : '';
                        $_methods[$_method] = $_methodLabel;
                    }
                }
            } catch (RestClientErrorException $e) {
                $this->logger->log(LogLevel::ERROR, $e->getMessage());

                /** @var Errors $response */
                $response = $responseParser->parse($e->getMessage(), Errors::class);
                throw AdapterException::errorResponse($response, $e);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage(), ['exception' => $e]);
                $_methods = [];
            }
        }
        return $_methods;
    }
}
