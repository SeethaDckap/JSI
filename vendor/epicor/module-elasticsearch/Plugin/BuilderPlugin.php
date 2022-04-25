<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Plugin;

use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;

class BuilderPlugin
{

    /**
     * @var string
     */
    const SPECIAL_CHARACTERS = '-+~/\\<>\'":*$#@()!,.?`=%&^';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var LocaleResolver
     */
    protected $localeResolver;

    /**
     * @var EsConfigInterface
     */
    protected $esConfig;

    /**
     * Current store ID.
     *
     * @var int
     */
    protected $storeId;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        LocaleResolver $localeResolver,
        EsConfigInterface $esConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->localeResolver = $localeResolver;
        $this->esConfig = $esConfig;
    }

    public function afterBuild(
        \Magento\Elasticsearch\Model\Adapter\Index\Builder $subject, $result
    )
    {
        //   $this->storeId = $subject->getStoreId();
        $tokenizer = $this->getTokenizer();
        $filter = $this->getFilter();
        $charFilter = $this->getCharFilter();
        if (isset($result['analysis']['analyzer']['default']['tokenizer'])) {
            $result['analysis']['analyzer']['default']['tokenizer'] = key($tokenizer);
            $result['analysis']['analyzer']['ecc_analyzer'] = ["tokenizer" => "ecc_analyzer",
                'filter' => array_merge(
                    ['lowercase', 'keyword_repeat'],
                    array_keys($filter)
                ),
                'char_filter' => array_keys($charFilter)
            ];
        }
        if (isset($result['analysis']['tokenizer'])) {
            $result['analysis']['tokenizer'] = $tokenizer;
        }
        return $result;
    }


    /**
     * @return array
     */
    protected function getFilter()
    {
        $filter = [
            'default_stemmer' => $this->getStemmerConfig(),
            'unique_stem' => [
                'type' => 'unique',
                'only_on_same_position' => true
            ]
        ];
        return $filter;
    }


    /**
     * @return array
     */
    protected function getStemmerConfig()
    {
        $stemmerInfo = $this->esConfig->getStemmerInfo();
        $this->localeResolver->emulate($this->storeId);
        $locale = $this->localeResolver->getLocale();
        if (isset($stemmerInfo[$locale])) {
            return [
                'type' => $stemmerInfo['type'],
                'language' => $stemmerInfo[$locale],
            ];
        }
        return [
            'type' => $stemmerInfo['type'],
            'language' => $stemmerInfo['default'],
        ];
    }

    /**
     * @return array
     */
    protected function getCharFilter()
    {
        $charFilter = [
            'default_char_filter' => [
                'type' => 'html_strip',
            ],
        ];
        return $charFilter;
    }

    /**
     * @return array
     */
    protected function getTokenizer()
    {
        $separators = $this->scopeConfig->getValue(
            'catalog/search/ecc_separators',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $tokenize_on_chars = [' '];
        if ($separators) {
            $tokenize_on_chars = str_split($separators, 1);
            $tokenize_on_chars[] = ' ';
        }
        $tokenizer = [
            'default_tokenizer' => [
                'type' => 'standard',
            ],
            'ecc_analyzer' => [
                'type' => 'char_group',
                'tokenize_on_chars' => $tokenize_on_chars
            ],
        ];
        return $tokenizer;
    }
}