<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Listskuproducts extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    /** @var \Magento\Framework\View\LayoutFactory */
    protected $layoutFactory;

    /**
     * @param \Epicor\Comm\Controller\Adminhtml\Context $context
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory
     * @param \Magento\Backend\Helper\Js $backendJsHelper
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
     * @param \Epicor\Comm\Helper\Data $commHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Store\CollectionFactory $commResourceCustomerErpaccountStoreCollectionFactory
     * @param \Epicor\Comm\Model\Customer\Erpaccount\StoreFactory $commCustomerErpaccountStoreFactory
     * @param \Epicor\SalesRep\Model\ResourceModel\Erpaccount\CollectionFactory $salesRepResourceErpaccountCollectionFactory
     * @param \Epicor\SalesRep\Model\ErpaccountFactory $salesRepErpaccountFactory
     * @param \Epicor\Common\Helper\Data $commonHelper
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    
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
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
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
            $resourceConfig
        );
         $this->layoutFactory = $layoutFactory;
    }

    public function execute()
    {
        if ($this->getRequest()->get('grid')) {
            $this->getResponse()->setBody(
                $this->layoutFactory->create()->createBlock('Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Sku\Products\Grid')->toHtml()
            );
        } else {
            $this->getResponse()->setBody(
                $this->layoutFactory->create()->createBlock('Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Sku\Products')->toHtml()
            );
        }
    }

}
