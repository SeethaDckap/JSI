<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class GetRegions extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Store\CollectionFactory $commResourceCustomerErpaccountStoreCollectionFactory,
        \Epicor\Comm\Model\Customer\Erpaccount\StoreFactory $commCustomerErpaccountStoreFactory,
        \Epicor\SalesRep\Model\ResourceModel\Erpaccount\CollectionFactory $salesRepResourceErpaccountCollectionFactory,
        \Epicor\SalesRep\Model\ErpaccountFactory $salesRepErpaccountFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Epicor\AccessRight\Model\RoleModel\Erp\AccountFactory $accessroleErpAccountFactory)
    {
        $this->directoryHelper = $directoryHelper;
        parent::__construct(
            $context,
            $backendAuthSession,
            $commCustomerErpaccountFactory,
            $backendJsHelper,
            $customerResourceModelCustomerCollectionFactory,
            $customerCustomerFactory,
            $commHelper,
            $scopeConfig,
            $commResourceCustomerErpaccountStoreCollectionFactory,
            $commCustomerErpaccountStoreFactory,
            $salesRepResourceErpaccountCollectionFactory,
            $salesRepErpaccountFactory,
            $commonHelper,
            $resourceConfig,
            $accessroleErpAccountFactory);
    }

    public function execute()
    {
        return trim($this->directoryHelper->getRegionJson());
    }

    }
