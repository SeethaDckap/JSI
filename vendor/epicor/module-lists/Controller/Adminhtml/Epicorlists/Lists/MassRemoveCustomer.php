<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class MassRemoveCustomer extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
    ) {
        $this->customerCustomerFactory = $customerCustomerFactory;
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Remove Customer Lists
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('listid');
        $customerId = $this->getRequest()->getParam('remove_customer');

        $customer = $this->customerCustomerFactory->create()->load($customerId);
        /* @var $customer Epicor_Comm_Model_Customer */

        if ($customer->isObjectNew()) {
            $this->messageManager->addError(__('Please select a Customer.'));
        } else {
            $customer->removeLists($ids);
            $customer->saveLists();
            $this->messageManager->addSuccess(__('Customer removed from ' . count($ids) . ' Lists '));
        }

        $this->_redirect('*/*/');
    }

    }
