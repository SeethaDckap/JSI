<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 4)
namespace Epicor\Comm\Model\GlobalConfig;


class Config extends \Magento\Framework\Config\Data
{
    public function __construct(
        \Epicor\Comm\Model\GlobalConfig\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'epicor_global_config'
    )
    {
        parent::__construct($reader, $cache, $cacheId);
    }

    public function get($path = null, $default = null)
    {
        $path = 'global/'.$path;

        return parent::get($path, $default);
    }
}
//M1 > M2 Translation End