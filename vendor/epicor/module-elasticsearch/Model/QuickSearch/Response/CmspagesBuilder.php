<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\QuickSearch\Response;

use Epicor\Elasticsearch\Api\QuickSearchResponseBuilderInterface;
use Magento\Search\Model\QueryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Framework\Search\Request\Query\BoolExpression;

/**
 * Implementation class that adds Cms Pages Section to Quick Search
 *
 */
class CmspagesBuilder extends AbstractBuilder implements QuickSearchResponseBuilderInterface
{
    /**
     * CMS Page Index Id
     */
    const ELASTICSEARCH_TYPE_CMS_PAGES = 'cms_pages';

    /**
     * System config path to CMS Page Content Length
     */
    const XML_PATH_DESCRIPTION_LENGTH = 'catalog/search/ecc_cms_pages_contentlength';

    /**
     * Category auto suggestion Enabled/Disabled Config Path
     */
    const XML_PATH_CONFIG_PATH_ENABLED = 'catalog/search/ecc_cms_pages_enabled';

    /**
     * CMS Pages Max Results Config Path
     */
    const XML_PATH_MAX_RESULTS = 'catalog/search/ecc_cms_pages_maxresults';

    /**
     * CMS Page Content Heading Length Config Path
     */
    const XML_PATH_NAME_LENGTH = 'catalog/search/ecc_cms_pages_namelength';

    /**
     * @var string[]
     */
    private $cmsPageFields = [
        'title',
        'content_heading',
        'content'
    ];

    /**
     * @var int
     */
    private $contentHeadingLength;

    /**
     * @var int
     */
    private $contentLength;



    /**
     * @inheritdoc
     */
    public function buildQuickSearchResponse()
    {
        return $this->getItems($this->query);
    }

    /**
     * Gets CMS Pages
     * @param QueryInterface $query
     * @return \Magento\Search\Model\QueryResult[]
     */
    private function getItems(QueryInterface $query)
    {
        $pagesResult = ["cmspages" => []];
        if ($this->isAutoSuggestionAllowed() && $query->getQueryText() != '') {
            $cmsPagesOptions = $this->getCmsPagesOptions($query);
            $pagesResult = [];
            if (is_array($cmsPagesOptions) && isset($cmsPagesOptions['hits']['hits'])) {
                foreach ($cmsPagesOptions['hits']['hits']  as $hits) {
                    $data = isset($hits['_source']) ? $hits['_source'] : [];
                    if (empty($data) && $data['is_active'] != 1) continue;
                    $pagesResult[] =
                        [
                            'queryText' => $this->getCmsPageHeading($data),
                            'page_id' => $data['page_id'],
                            'url' => $this->getCmsPageUrl($data),
                            'content' => $this->getCmsPageContent($data)
                        ];
                }
            }
            $pagesResult = ["cmspages" => $pagesResult];
        }
        return $pagesResult;
    }

    /**
     * Get CMS Pages Options
     * @param QueryInterface $query
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCmsPagesOptions(QueryInterface $query)
    {
        $searchQuery = $this->initQuery();
        $searchQuery['body']['query'] = array_merge(
            $searchQuery['body']['query'],
            $this->prepareQuery($query)
        );
        return $this->fetchQuery($searchQuery);
    }

    /**
     * Sorting of result
     * @return \string[][]
     */
    private function getSort()
    {
        return [
            '_score' => [
                'order' => 'desc'
            ],
            'page_id' => [
                'order' => 'desc',
                'missing' => '_first',
                'unmapped_type' => 'keyword'
            ]
        ];
    }

    /**
     * Init Query
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function initQuery()
    {
        $searchSuggestionsCount = $this->getSearchSuggestionsCount();
        return [
            'index' => $this->searchIndexNameResolver->getIndexName(
                $this->storeManager->getStore()->getId(),
                self::ELASTICSEARCH_TYPE_CMS_PAGES
            ),
            'type' => Config::ELASTICSEARCH_TYPE_DOCUMENT,
            'body' => [
                'from' => 0,
                'size' => $searchSuggestionsCount,
                'query' => [],
                'sort' => $this->getSort()
            ]
        ];
    }

    /**
     * Prepare Include Term Query.
     *
     * @param $query
     *
     * @return array
     */
    private function prepareQuery($query)
    {
        $excludeIds = $this->getExcludePageId();
        $ids = $this->getIncludePageIds($excludeIds);
        if ($excludeIds && $ids) {
            return [
                'bool' => [
                    BoolExpression::QUERY_CONDITION_MUST => [
                        ['terms' => ['page_id' => $ids]],
                        $this->prepareShouldQuery($query),
                    ],
                ],
            ];
        }

        return $this->prepareShouldQuery($query);
    }

    /**
     * Prepare Should Search text Query.
     *
     * @param $query
     *
     * @return array
     */
    private function prepareShouldQuery($query)
    {
        return [
            'bool' => [
                BoolExpression::QUERY_CONDITION_SHOULD => $this->getCmsPageQuery($query),
                'adjust_pure_negative'                 => true,
                'minimum_should_match'                 => "1",
                'boost'                                => 1,
            ],
        ];
    }

    /**
     * Building Query
     * @param $query
     * @return array
     */
    private function getCmsPageQuery($query)
    {
        $cmsPageQuery = [];
        $word = $query->getQueryText();
        $queries = explode(" ", $word);
        foreach ($this->cmsPageFields as $field) {
            foreach ($queries as $queryText) {
                $values = [];
                $queryText = strtolower($queryText) . '*';
                $values['wildcard'][$field]['wildcard'] = $queryText;
                $values['wildcard'][$field]['boost'] = 2;
                $cmsPageQuery[] = $values;
            }
        }
        return $cmsPageQuery;
    }

    /**
     * Returns CMS Page Content Heading
     * @param $data
     * @return false|string|void
     */
    private function getCmsPageHeading($data)
    {
        $title = '';
        if (isset($data['title'])) {
            $title = $data['title'];
            $length = $this->getNameLength();
            if (strlen($title) > $length) {
                $title = $this->truncateData($data['title'], $length) . "...";
            }
        }
        return $title;
    }
    /**
     * Gets CMS Pages Url
     * @param $data
     * @return string|void
     */
    private function getCmsPageUrl($data)
    {
        $url = '';
        if (isset($data['identifier'])) {
            $url = $this->storeManager->getStore()->getBaseUrl() . $data['identifier'];
        }
        return $url;
    }

    /**
     * Gets the CMS page content
     * @param $data
     * @return string|void
     */
    private function getCmsPageContent($data)
    {
        $content = '';
        if (isset($data['content'])) {
            $content = strip_tags($data['content']);
            $content = trim($content);
            $length = $this->getDescriptionLength();
            if (strlen($content) > $length) {
                $content = $this->truncateData($content, $length) . "...";
            }
        }
        return $content;
    }

    /**
     * Include CMS Pages.
     *
     * @param $excludeIds
     *
     * @return array|null
     */
    private function getIncludePageIds($excludeIds)
    {
        $isExclude = false;
        if (!$excludeIds) {
            return null;
        }

        $allCmsIds = $this->searchHelperData->getAllCmsPageIds();
        foreach (explode(",", $excludeIds) as $cmsId) {
            if (($key = array_search($cmsId, $allCmsIds)) !== false) {
                unset($allCmsIds[$key]);
                $isExclude = true;
            }
        }

        $return = [];
        foreach ($allCmsIds as $val) {
            $return[] = $val;
        }

        if($isExclude && !$return) {
            $return[] = "0";
        }

        return $return;
    }
}