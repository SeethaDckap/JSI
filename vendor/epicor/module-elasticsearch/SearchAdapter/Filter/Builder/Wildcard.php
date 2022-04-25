<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Request\Filter\Wildcard as WildcardFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Epicor\Elasticsearch\Helper\Data as Helper;

class Wildcard extends \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Wildcard
{
    /**
     * @var FieldMapperInterface
     */
    protected $fieldMapper;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var array
     */
    private $_words = [];

    /**
     * @var string
     */
    private $_queryText = "";

    /**
     * Wildcard constructor.
     * @param FieldMapperInterface $fieldMapper
     * @param Helper $helper
     */
    public function __construct(
        FieldMapperInterface $fieldMapper,
        Helper $helper
    )
    {
        $this->fieldMapper = $fieldMapper;
        $this->helper = $helper;
        parent::__construct($fieldMapper);
    }

    /**
     * @param RequestFilterInterface|WildcardFilterRequest $filter
     * @return array
     */
    public function buildFilter(RequestFilterInterface $filter)
    {
        $fieldName = $this->fieldMapper->getFieldName($filter->getField());
        $queryText = $filter->getValue();
        $this->_queryText = addcslashes($queryText, "\000\n\r\\'\"\032");
        $this->_words = $this->helper->parseString($queryText);
        $result = [];
        foreach ($this->_words as $word) {
            $word = strtolower($word);
            $word = $this->helper->setPrefixAndSuffix($word);
            $result[] =
                [
                    'wildcard' => [
                        $fieldName => $word,
                    ],
                ];
        }
        return $result;
    }
}
