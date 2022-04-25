<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{
    private $_fileFactory;

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
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Epicor\AccessRight\Model\RoleModel\Erp\AccountFactory $accessroleErpAccountFactory
    ) {
        $this->_fileFactory = $fileFactory;
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
            $accessroleErpAccountFactory
        );
    }


    /**
     * Export customer grid to CSV format
     */
    public function execute()
    {
        $fileName = 'erpaccounts.csv';
        //M1 > M2 Translation Begin (Rule 2)
        //$content = $this->getLayout()->createBlock('epicor_comm/adminhtml_customer_erpaccount_grid')
        //    ->getCsvFile();

        //$this->_prepareDownloadResponse($fileName, $content);

        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->createBlock('Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Grid');

        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getCsvFile(),
            DirectoryList::VAR_DIR
        );
        //M1 > M2 Translation End
    }

    }
