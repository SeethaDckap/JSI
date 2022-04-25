<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Api;

/**
 * Interface for managing quick search.
 * @api
 */
interface QuickSearchResponseBuilderInterface
{
    /**
     * Returns Quick Search Result Response
     *
     * @api
     * @return array
     */
    public function buildQuickSearchResponse();
}