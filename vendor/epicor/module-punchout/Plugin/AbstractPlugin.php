<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Plugin;

use Magento\Framework\UrlInterface;

class AbstractPlugin
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;


    /**
     * Constructor function.
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param EUrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        UrlInterface $urlBuilder
    )
    {
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
    }

}
