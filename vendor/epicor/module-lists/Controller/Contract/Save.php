<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Contract;

class Save extends \Epicor\Lists\Controller\Contract
{

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->generic = $generic;
    }
    /**
     *  Default Contract Save 
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $customerId = $this->customerSession->getId();
            $customer = $this->customerCustomerFactory->create()->load($customerId);
            $customer->setEccDefaultContract($data['contract_default']);
            $customer->setEccDefaultContractAddress($data['contract_default_address']);
            $customer->save();
            $session = $this->generic;
            $session->addSuccess(__('Default Contract Saved Successfully'));
            $this->_redirect('*/*/');
        } else {
            $this->_redirect('*/*/');
        }
    }

    }
