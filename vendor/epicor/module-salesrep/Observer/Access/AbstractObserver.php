<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Access;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $_noAccess = array(
        'customerconnect/access_management/'
    );
    protected $_allowedUrls = array(
        'salesrep',
        'customerconnect/rfqs',
        'customer/account/',
        'customer/account/logout/'
    );
    protected $_disallowedUrls = array(
        'customerconnect/rfqs/',
    );
    protected $_disallowedSearchUrls = array(
        'wishlist',
        'quickorderpad',
    );

    protected $salesRepHelper;

    protected $customerSession;

    protected $request;

    protected $commHelper;

    protected $scopeConfig;

    public function __construct(
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->salesRepHelper = $salesRepHelper;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->commHelper = $commHelper;
        $this->scopeConfig = $scopeConfig;
    }


    protected function _isUrlAllowed($allowed, $url, $masq)
    {
        foreach ($this->_allowedUrls as $search) {
            if (strpos($url, $search) !== false) {
                $allowed = true;
            }
        }

        if (!$masq) {
            if (in_array($url, $this->_disallowedUrls)) {
                $allowed = false;
            } else {
                foreach ($this->_disallowedSearchUrls as $search) {
                    if (strpos($url, $search) !== false) {
                        $allowed = false;
                    }
                }
            }
        } else {
            if (in_array($url, $this->_noAccess)) {
                $allowed = false;
            }
        }

        return $allowed;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // TODO: Implement execute() method.
    }


}

