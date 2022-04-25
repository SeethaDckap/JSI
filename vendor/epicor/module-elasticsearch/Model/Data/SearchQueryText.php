<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\Data;

/**
 * Search string save in to object.
 * For retrieving when ES Boosting Sku with *
 *
 * Class SearchQueryText
 *
 * @package Epicor\Elasticsearch\Model\Data
 */
class SearchQueryText
{
    /**
     * @var string|null
     */
    private $queryText;

    /**
     * @return mixed
     */
    public function getQueryText()
    {
        return $this->queryText;
    }

    /**
     * @param $queryText
     */
    public function setQueryText($queryText)
    {
        $this->queryText = $queryText;
    }

}
