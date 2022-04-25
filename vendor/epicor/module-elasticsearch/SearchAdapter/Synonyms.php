<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elasticsearch\SearchAdapter;

use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;
use Magento\Search\Api\SynonymAnalyzerInterface;

class Synonyms implements PreprocessorInterface
{
    /**
     * @var SynonymAnalyzerInterface
     */
    private $synonymsAnalyzer;

    /**
     * Constructor
     *
     * @param SynonymAnalyzerInterface $synonymsAnalyzer
     */
    public function __construct(SynonymAnalyzerInterface $synonymsAnalyzer)
    {
        $this->synonymsAnalyzer = $synonymsAnalyzer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($query)
    {
        $synonyms = [];
        $synonymsArray = $this->synonymsAnalyzer->getSynonymsForPhrase($query);
        if (count($synonymsArray) > 0) {
            foreach ($synonymsArray as $synonymPart) {
                $synonyms [] = implode(' ', $synonymPart);
            }
            $query = implode(' ', $synonyms);
        }
        return $query;
    }

    /**
     * Retrieves Synonyms for the provided search term
     *
     * @param string $query
     * @return array
     */
    public function synonymsForPhrase($query)
    {
        $synonymsArray = $this->synonymsAnalyzer->getSynonymsForPhrase($query);
        return $synonymsArray;
    }
}
