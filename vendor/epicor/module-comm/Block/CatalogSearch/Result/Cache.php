<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\CatalogSearch\Result;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Block to handle search results cache tags.
 *
 */
class Cache extends AbstractBlock implements IdentityInterface
{
    /**
     * @var string
     */
    const CACHE_TAG = 'ecc_search_boost_rules';

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * Cache constructor.
     *
     * @param \Magento\Framework\View\Element\Context  $context
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\App\ResponseInterface $response,
        array $data = []
    ) {
        $this->response = $response;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->isPageCacheable()) {
            $identities = array_merge($identities, [self::CACHE_TAG]);
        }
        return $identities;
    }

    /**
     * Check if current page is cacheable
     *
     * @return bool
     */
    public function isPageCacheable()
    {
        $result = false;
        $pragma = $this->response->getHeader('pragma');
        if ($pragma) {
            $result = $pragma->getFieldValue() === 'cache';
        }
        return $result;
    }
}

