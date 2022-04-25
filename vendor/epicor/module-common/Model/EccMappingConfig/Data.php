<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\EccMappingConfig;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides catalog attributes configuration
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\Attribute\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Epicor\Common\Model\EccMappingConfig\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'ecc_data_mapping',
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }
}
