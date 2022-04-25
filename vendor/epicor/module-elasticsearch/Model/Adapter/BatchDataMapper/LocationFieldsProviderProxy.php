<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\Adapter\BatchDataMapper;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;

class LocationFieldsProviderProxy implements AdditionalFieldsProviderInterface
{
    /**
     * @var ClientResolver
     */
    private $clientResolver;

    /**
     * @var AdditionalFieldsProviderInterface[]
     */
    private $locationFieldsProviders;

    /**
     * LocationFieldsProviderProxy constructor.
     * @param ClientResolver $clientResolver
     * @param array $locationFieldsProviders
     */
    public function __construct(
        ClientResolver $clientResolver,
        array $locationFieldsProviders
    ) {
        $this->clientResolver = $clientResolver;
        $this->locationFieldsProviders = $locationFieldsProviders;
    }

    /**
     * @return AdditionalFieldsProviderInterface
     */
    private function getlocationFieldsProvider()
    {
        return $this->locationFieldsProviders[$this->clientResolver->getCurrentEngine()];
    }

    /**
     * @inheritdoc
     */
    public function getFields(array $productIds, $storeId)
    {
        return $this->getlocationFieldsProvider()->getFields($productIds, $storeId);
    }
}
