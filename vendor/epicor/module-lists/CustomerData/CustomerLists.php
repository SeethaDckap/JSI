<?php

/**
 * Copyright © 2010-2019 Epicor Software. All rights reserved.
 */

namespace Epicor\Lists\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerLists implements SectionSourceInterface
{

    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_lists_read';

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    private $listsFrontendRestrictedHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customer;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    private $accessauthorization;

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * Grid Collection
     *
     * @var \Epicor\Lists\Model\ResourceModel\SidebarLists
     */
    private $collection;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ChooseAddressLink constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper
     * @param \Epicor\Comm\Model\Customer $customer
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Epicor\AccessRight\Model\Authorization $authorization
     * @param \Epicor\Lists\Model\ResourceModel\SidebarLists $collection
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Epicor\Comm\Model\Customer $customer,
        \Magento\Framework\App\Http\Context $httpContext,
        \Epicor\AccessRight\Model\Authorization $authorization,
        \Epicor\Lists\Model\ResourceModel\SidebarLists $collection
    )
    {
        $this->customerSession = $customerSession;
        $this->listsFrontendRestrictedHelper = $listsFrontendRestrictedHelper;
        $this->customer = $customer;
        $this->httpContext = $httpContext;
        $this->accessauthorization = $authorization;
        $this->collection = $collection;
        $this->scopeConfig = $this->listsFrontendRestrictedHelper->getScopeConfig();

    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        $accountSummaryInfo = array("is_enable" => false);
        $isAccessAllow = $this->accessauthorization->isAllowed(static::FRONTEND_RESOURCE);
        $isAllowed = $this->isChangeAddressAllowed();
        if ($isAllowed && $this->httpContext->getValue(Context::CONTEXT_AUTH) && $isAccessAllow) {
            $accountSummaryInfo["is_enable"] = true;
            $accountSummaryInfo['items'] = $this->getItems();
        }

        return $accountSummaryInfo;
    }

    /**
     * Use Reference \Epicor\Lists\Observer\ModifyBlockHtmlBefore
     * with $this->>topLinkAllowed
     *
     * @return bool|null
     */
    public function isChangeAddressAllowed()
    {
        $customerId = $this->customerSession->getCustomerId();
        $customerModel = $this->customer->load($customerId);
        if (
            $customerModel->isSupplier() ||
            $customerModel->isSalesRep() ||
            !$this->listsFrontendRestrictedHelper->listsEnabled() ||
            !$this->scopeConfig->isSetFlag('epicor_lists/global/lists_widget_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Get list of Lists
     *
     * @return array
     */
    protected function getItems()
    {
        $items = [];
        $listsCollections = $this->collection->getCollection();
        $listsCollections->getSelect()->limit(\Epicor\Lists\Model\ResourceModel\SidebarLists::SIDEBAR_LISTS_LIMIT);
        foreach ($listsCollections as $lists) {
            $items[] = [
                'id' => $lists->getId(),
                'name' => $lists->getTitle()
            ];
        }
        return $items;
    }
}