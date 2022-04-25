<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer\Customer;

/**
 * Sets a Customers Customer Group based on their ERP Account
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class SetCustomerGroup implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Epicor\Comm\Helper\DataFactory
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    public function __construct(
    \Epicor\Comm\Helper\DataFactory $commHelper,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Framework\App\Request\Http $request
    )
    {
        $this->commHelper = $commHelper;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        $action = $this->request->getActionName();

        $force = $customer->getForceErpAccountGroup() || $action == 'createpost';
        $useMultipleGroups = $this->scopeConfig->getValue('epicor_comm_field_mapping/cus_mapping/customer_use_multiple_customer_groups',
                                                          \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        /* @var $commHelper \Epicor\Comm\Helper\Data */
        $commHelper = $this->commHelper->create();

        if ($useMultipleGroups || $force) {

            /* If no default ERP account sset in Quick Start Customer Configuration */
            $isDefaultErpAccountSet = $this->scopeConfig->getValue('customer/create_account/default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($isDefaultErpAccountSet === null) {
                return $this;
            }

            $erpAccount = $commHelper->getErpAccountInfo($customer->getEccErpaccountId());

            if ($erpAccount) {
                $customer->setGroupId($erpAccount->getMagentoId());
            }
        }

        return $this;
    }

}
