<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model;

/*
 * CookieManager class
 *
 */

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class CookieManager
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;


    /**
     * CookieManager constructor.
     *
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */

    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    )
    {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }


    /**
     * get cookieManager.
     *
     * @param CookieManagerInterface
     */
    public function getCookieManager()
    {
        return $this->cookieManager;
    }


    /**
     * get CookieMetadataFactory.
     *
     * @param CookieMetadataFactory
     */
    public function getCookieMetadataFactory()
    {
        return $this->cookieMetadataFactory;
    }
}

