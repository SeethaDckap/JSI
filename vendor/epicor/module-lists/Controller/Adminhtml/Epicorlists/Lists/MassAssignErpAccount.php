<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class MassAssignErpAccount extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Epicor\Lists\Helper\Admin
     */
    protected $listsAdminHelper;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Epicor\Lists\Helper\Admin $listsAdminHelper
    ) {        
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->listsAdminHelper = $listsAdminHelper;
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Assign ERP Account Lists
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('listid');
        $erpAccountId = $this->getRequest()->getParam('assign_erp_account');
        
        $erpAccount = $this->commCustomerErpaccountFactory->create()->load($erpAccountId);
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

        if ($erpAccount->isObjectNew()) {
            $this->messageManager->addError(__('Please select an Erp Account.'));
        } else {
            $returnvalues = $this->listsAdminHelper->assignErpAccountListsCheck($ids, $erpAccount);
            if ($returnvalues) {
                if (!empty($returnvalues['success']['id'])) {
                    $erpAccount->addLists($returnvalues['success']['values']);
                    $erpAccount->save();
                    $this->messageManager->addSuccess(__('ERP Account assigned to ' . count(array_keys($returnvalues['success']['values'])) . ' Lists. ' . "List Id: (" . $returnvalues['success']['id'] . ")"));
                }
                if (!empty($returnvalues['error']['id'])) {
                    $this->messageManager->addError(__('ERP Account not assigned to ' . count(array_keys($returnvalues['error']['values'])) . ' Lists. ' . "List Id: (" . $returnvalues['error']['id'] . ")"));
                }
            }
        }

        $this->_redirect('*/*/');
    }

}
