<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Observer\Cuos;

abstract class AbstractObserver implements \Magento\Framework\Event\ObserverInterface {
    /*
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /*
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealerHelper;

    public function __construct(
    \Epicor\Dealerconnect\Helper\Data $dealerHelper, \Magento\Customer\Model\Session $customerSession
    ) {
        $this->dealerHelper = $dealerHelper;
        $this->customerSession = $customerSession;
    }

}
