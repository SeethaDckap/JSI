<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep;

class MassAssignToErpAccounts  extends \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep
{

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Erpaccount\CollectionFactory
     */
    protected $salesRepResourceErpaccountCollectionFactory;

    /**
     * @var \Epicor\SalesRep\Model\ErpaccountFactory
     */
    protected $salesRepErpaccountFactory;

    public function __construct(
        \Epicor\SalesRep\Model\ResourceModel\Erpaccount\CollectionFactory $salesRepResourceErpaccountCollectionFactory,
        \Epicor\SalesRep\Model\ErpaccountFactory $salesRepErpaccountFactory,
        \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Context $context
    )
    {
        $this->salesRepResourceErpaccountCollectionFactory = $salesRepResourceErpaccountCollectionFactory;
        $this->salesRepErpaccountFactory = $salesRepErpaccountFactory;
        parent::__construct($context);
    }

    public function execute()
    {

        $erpAccountsIds = $this->getRequest()->getPost('accounts');
        $salesRepAccountId = $this->getRequest()->getPost('sales_rep_account');

        if (!$salesRepAccountId) {
            $this->messageManager->addErrorMessage(__('The Sales Rep is required.'));
            $this->_redirect('adminhtml/epicorcomm_customer_erpaccount/index');
            return;
        }

        $erpAccountsExistent = $this->salesRepResourceErpaccountCollectionFactory->create()->getErpAccountsBySalesRepAccount($salesRepAccountId);

        $erpAccountsRemove = array();
        foreach ($erpAccountsExistent as $erpAccount) {
            $erpAccountsRemove[] = $erpAccount->getErpAccountId();
        }

        $erpAccountsIds = array_diff($erpAccountsIds, $erpAccountsRemove);

        foreach ($erpAccountsIds as $erpAccountId) {
            $model = $this->salesRepErpaccountFactory->create();
            $model->setErpAccountId($erpAccountId);
            $model->setSalesRepAccountId($salesRepAccountId)->save();
            $model->save();
        }

        $this->messageManager->addSuccessMessage(__('The Sales Rep have been assigned.'));
        $this->_redirect('adminhtml/epicorcomm_customer_erpaccount/index');
    }

}
