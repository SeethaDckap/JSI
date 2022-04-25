<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Observer;

use Epicor\SalesRep\Model\ResourceModel\Account\Collection;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class SalesRepHomePageCheck
 * @package Epicor\Common\Observer
 */
class SalesRepHomePageCheck implements ObserverInterface
{
    /**
     * Configuration path for Sales Rep browse catalog
     */
    const XML_PATH_SALESREP_CATALOG_ALLOWED = 'epicor_salesrep/general/catalog_allowed';

    /**
     * @var Http
     */
    private $request;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Collection
     */
    private $salesRepAccount;

    /**
     * SalesRepHomePageCheck constructor.
     * @param Http $request
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param ResponseInterface $response
     * @param UrlInterface $url
     * @param Collection $salesRepAccount
     */
    public function __construct(
        Http $request,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        ResponseInterface $response,
        UrlInterface $url,
        Collection $salesRepAccount
    ) {
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->response = $response;
        $this->url = $url;
        $this->salesRepAccount = $salesRepAccount;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $canBrowseCatalog = $this->scopeConfig->getValue(self::XML_PATH_SALESREP_CATALOG_ALLOWED, ScopeInterface::SCOPE_STORE);
        if (($this->url->getCurrentUrl() == $this->url->getBaseUrl(['_secure' => $this->request->isSecure()])) &&
            ($this->customerSession->isLoggedIn()) &&
            ($this->customerSession->getCustomer()->isSalesRep()) &&
            (!$this->customerSession->getMasqueradeAccountId())) {
            if ($canBrowseCatalog == 'forceN') {
                $this->response->setRedirect($this->url->getUrl('salesrep/account'), 403);
                $this->response->sendResponse();
            } else if ($canBrowseCatalog == 'N' || $canBrowseCatalog == 'Y') {
                $salesRepAcc = $this->salesRepAccount
                    ->addFieldToFilter('id', $this->customerSession->getCustomer()->getEccSalesRepAccountId())
                    ->getFirstItem();
                if ($salesRepAcc->getCatalogAccess() != 'Y') {
                    $this->response->setRedirect($this->url->getUrl('salesrep/account'), 403);
                    $this->response->sendResponse();
                }
            }
        }
    }
}
