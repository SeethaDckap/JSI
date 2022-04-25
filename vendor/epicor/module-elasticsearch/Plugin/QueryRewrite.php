<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Plugin;

use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper as QueryBuilder;
use Epicor\Elasticsearch\Model\ResourceModel\Boost\CollectionFactory;
use Epicor\Elasticsearch\Api\Data\BoostInterface;
use Magento\Framework\App\Request\Http as Request;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Framework\Search\RequestInterface;
use Epicor\Elasticsearch\Model\Data\SearchQueryText;
/**
 * Plugin that handles query rewriting for function score
 *
 */
class QueryRewrite
{
    /**
     * @var CollectionFactory
     */
    private $boostCollectionFactory;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var Request
     */
    private $request;

    /**
     * Provider constructor.
     * @var SearchQueryText
     */
    private $searchQueryText;

    /**
     * Provider constructor.
     *
     * @param CollectionFactory $boostCollectionFactory
     * @param Request $request
     * @param StoreManager $storeManager
     */
    public function __construct(
        CollectionFactory $boostCollectionFactory,
        Request $request,
        StoreManager $storeManager,
        SearchQueryText $searchQueryText
    )
    {
        $this->boostCollectionFactory = $boostCollectionFactory;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->searchQueryText = $searchQueryText;
    }

    /**
     * Rewrite build query to add function
     *
     * @param QueryBuilder     $subject
     * @param array            $searchQuery
     * @param RequestInterface $request
     *
     * @return array|mixed
     */
    public function afterBuildQuery(
        QueryBuilder $subject,
        $searchQuery,
        RequestInterface $request
    )
    {
        $subject;
        if ($this->isAllowed()) {
            $this->setQueryText($request);
            $getFunctions = $this->applyBoosts();
            $getFinalFunctions = array_values($getFunctions);
            if (count($getFinalFunctions) > 0) {
                $searchQuery['body']['query'] = array(
                    "function_score" =>
                        array(
                            "query" => $searchQuery['body']['query'],
                            "score_mode" => "multiply",
                            "boost_mode" => "multiply",
                            "functions" => $getFinalFunctions,
                            "boost" => 1
                        )
                );
            }
            return $searchQuery;
        } else {
            return $searchQuery;
        }
    }

    private function isAllowed()
    {
        $checkIfCatalogSearchAction = $this->request->getFullActionName();
        if ($checkIfCatalogSearchAction == "catalogsearch_result_index"
            || $checkIfCatalogSearchAction == "eccsearch_ajax_suggest"
        ) {
            $collection = $this->boostCollectionFactory->create();
            $collection->addIsActiveFilter();
            $collection->addFieldToFilter(
                BoostInterface::STORE_ID,
                $this->storeManager->getStore()->getId()
            );
            return $collection->getSize() > 0 ? true : false;
        } else {
            return false;
        }
    }

    private function applyBoosts()
    {
        $boosts = $this->getBoostsCollection();
        return $this->getBoostsFunctions($boosts);
    }

    /**
     * Get active boost records
     *
     * @return Collection
     */
    private function getBoostsCollection()
    {
        $collection = $this->boostCollectionFactory->create();
        $collection->addIsActiveFilter();
        $collection->addFieldToFilter(
            BoostInterface::STORE_ID,
            $this->storeManager->getStore()->getId()
        );
        return $collection;
    }

    private function getBoostsFunctions($boosts)
    {
        $functions = [];
        foreach ($boosts as $boost) {
            $function = $this->getFunction($boost);
            if ($function !== null) {
                $functions[$boost->getId()] = $function;
            }
        }
        return $functions;
    }

    private function getFunction(BoostInterface $boost)
    {
        $constant = $boost->getConfig('constant_score_value');
        if ($constant < -100) {
            $constant = -100;
        }
        return [
            'weight' => 1 + ((float)$constant / 100),
            'filter' => $boost->getRuleCondition()->getSearchQuery(),
        ];
    }

    /**
     * Request Search Query Text.
     *
     * @param $request
     */
    private function setQueryText($request) {
        if($request->getQuery() && $request->getQuery()->getShould() && isset($request->getQuery()->getShould()["search"])) {
            $this->searchQueryText->setQueryText($request->getQuery()->getShould()["search"]->getValue());
        }
    }
}
