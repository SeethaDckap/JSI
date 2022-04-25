<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class MassRemoveErpAccount extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory
    ) {
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Remove ERP Account Lists
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('listid');
        $erpAccountId = $this->getRequest()->getParam('remove_erp_account');

        $erpAccount = $this->commCustomerErpaccountFactory->create()->load($erpAccountId);
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

        if ($erpAccount->isObjectNew()) {
            $this->messageManager->addError(__('Please select an Erp Account.'));
        } else {
            $erpAccount->removeLists($ids);
            $erpAccount->save();
            $this->messageManager->addSuccess(__('ERP Account removed to ' . count($ids) . ' Lists '));
        }

        $this->_redirect('*/*/');
    }

}
