<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer;

class MassSetShopper extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Customer
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
    ) {
        parent::__construct($context, $backendAuthSession);
        $this->backendSession = $context->getSession();
        $this->customerCustomerFactory = $customerCustomerFactory;
    }
    public function execute()
    {

        $customersIds = $this->getRequest()->getParam('customer');
        if (!is_array($customersIds)) {
            $this->messageManager->addError(__('Please select customer(s).'));
        } else {
            try {

                foreach ($customersIds as $customerId) {
                    $customer = $this->customerCustomerFactory->create()->load($customerId);
                    $customer->setEccMasterShopper(1);
                    $customer->save();
                }

                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were updated.', count($customersIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('customer/index/index');
        return;  
    }

    }
