<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Contract;

class Filter extends \Epicor\Lists\Controller\Contract
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
     *  Filter Contract Save 
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $filter[$key] = implode(',', array_filter($this->getRequest()->getParam($key)));
                }
            }
            $customerId = $this->customerSession->getId();
            $customer = $this->customerCustomerFactory->create()->load($customerId);
            $customer->setEccContractsFilter($filter['contract_filter']);
            $customer->save();
            $session = $this->generic;
            $session->addSuccess(__('Filter Contract Saved Successfully'));
            $this->_redirect('*/*/');
        } else {
            $this->_redirect('*/*/');
        }
    }

    }
