<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class MassAssignCustomer extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
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
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Lists\Helper\Admin
     */
    protected $listsAdminHelper;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Lists\Helper\Admin $listsAdminHelper
    ) {
        $this->listsHelper = $listsHelper;
        $this->backendSession = $context->getBackendSession();
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->listsAdminHelper = $listsAdminHelper;
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Assign Customer Lists
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('listid');
        $customerId = $this->getRequest()->getParam('assign_customer');

        $customer = $this->customerCustomerFactory->create()->load($customerId);
        /* @var $customer \Epicor\Comm\Model\Customer */

        if ($customer->isObjectNew()) {
            $this->messageManager->addError(__('Please select a Customer.'));
        } else {
            $returnvalues = $this->listsAdminHelper->assignCustomerAccountListsCheck($ids, $customer);
            if ($returnvalues) {
                if (!empty($returnvalues['success']['id'])) {
                    $customer->addLists($returnvalues['success']['values']);
                    $customer->saveLists();
                    $this->messageManager->addSuccess(__('Customer Account assigned to ' . count(array_keys($returnvalues['success']['values'])) . ' Lists. ' . "List Id: (" . $returnvalues['success']['id'] . ")"));
                }
                if (!empty($returnvalues['error']['id'])) {
                    $this->messageManager->addError(__('Customer Account not assigned to ' . count(array_keys($returnvalues['error']['values'])) . ' Lists. ' . "List Id: (" . $returnvalues['error']['id'] . ")"));
                }
            }
        }

        $this->_redirect('*/*/');
    }

}
