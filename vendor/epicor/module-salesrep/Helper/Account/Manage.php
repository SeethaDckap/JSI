<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Helper\Account;


use Epicor\Comm\Helper\Context;

class Manage extends \Epicor\SalesRep\Helper\Data
{

    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */
    protected $salesRepAccountFactory;

    public function __construct(
        Context $context,
        \Epicor\SalesRep\Model\AccountFactory $salesRepAccountFactory
    ) {
        $this->salesRepAccountFactory = $salesRepAccountFactory;
        parent::__construct($context);
    }
    /**
     * Sets up the registry for a management page
     */
    public function registerAccounts()
    {
        $customerSession = $this->customerSessionFactory->create();
        $salesRepAccountId = $customerSession->getManageSalesRepAccountId();

        $baseSalesRepAccount = $this->getCustomerSalesRepAccount();
        /* @var $baseSalesRepAccount Epicor_SalesRep_Model */

        if (empty($salesRepAccountId) || !$baseSalesRepAccount->hasChildAccount($salesRepAccountId)) {
            $salesRepAccount = $baseSalesRepAccount;
        } else {
            $salesRepAccount = $this->salesRepAccountFactory->create()->load($salesRepAccountId);
        }

        $this->registry->register('sales_rep_account_base', $baseSalesRepAccount);
        $this->registry->register('sales_rep_account', $salesRepAccount);

        $editable = ($salesRepAccount->getId() != $baseSalesRepAccount->getId());
        $this->registry->register('sales_rep_editable', $editable);
    }

    /**
     * Encodes the provided ID
     * 
     * @param string $id
     * 
     * @return string
     */
    public function encodeId($id)
    {
        return base64_encode(serialize($id));
    }

    /**
     * Decodes the provided ID
     * 
     * @param string $id
     * 
     * @return string
     */
    public function decodeId($id)
    {
        return unserialize(base64_decode($id));
    }

    /**
     * 
     * @return \Epicor\SalesRep\Model\Account
     */
    public function getBaseSalesRepAccount()
    {
        return $this->registry->registry('sales_rep_account_base');
    }

    /**
     * 
     * @return \Epicor\SalesRep\Model\Account
     */
    public function getManagedSalesRepAccount()
    {
        return $this->registry->registry('sales_rep_account');
    }

    /**
     * Gets the sales rpe account of the current customer
     * 
     * @return \Epicor\SalesRep\Model\Account
     */
    public function getCustomerSalesRepAccount()
    {
        $customerSession = $this->customerSessionFactory->create();

        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        return $this->salesRepAccountFactory->create()->load($customer->getEccSalesRepAccountId());
    }

    /**
     * Returns whether a child account is currently being managed
     * 
     * @return boolean
     */
    public function isManagingChild()
    {
        $baseAccount = $this->getBaseSalesRepAccount();
        $managedAccount = $this->getManagedSalesRepAccount();

        return ($baseAccount->getId() != $managedAccount->getId());
    }

    public function canEdit()
    {
        return $this->registry->registry('sales_rep_editable');
    }

    public function canAddChildrenAccounts()
    {
        $salesRepAccount = $this->getCustomerSalesRepAccount();
        /* @var $salesRepAccount \Epicor\SalesRep\Model\Account */

        if ($this->scopeConfig->getValue('epicor_salesrep/management/frontend_children_create', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $salesRepAccount->isManager()) {
            return true;
        } else {
            return false;
        }
    }

}
