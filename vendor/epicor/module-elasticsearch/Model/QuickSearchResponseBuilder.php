<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model;

use Epicor\Elasticsearch\Api\QuickSearchResponseBuilderInterface;
use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Implementation class of the quick search service contract.
 */
class QuickSearchResponseBuilder implements QuickSearchResponseBuilderInterface
{
    /**
     * @var QuickSearchResponseBuilderInterface[] | TMap
     */
    private $builders;

    /**
     * QuickSearchResponseBuilder constructor.
     * @param TMapFactory $tmapFactory
     * @param array $builders
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $builders = []
    )
    {
        $this->builders = $tmapFactory->create(
            [
                'array' => $builders,
                'type' => QuickSearchResponseBuilderInterface::class
            ]
        );
    }

    /**
     * Return aggregated response for various sections of quick search
     *
     * @api
     * @return array
     */
    public function buildQuickSearchResponse()
    {
        $result = [];
        foreach ($this->builders as $builder) {
            $result[] = $builder->buildQuickSearchResponse();
        }
        return $result;
    }
}