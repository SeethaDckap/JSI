<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassAssignGroup
 */
class MassAssignGroup extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Customer
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        CustomerRepositoryInterface $customerRepository
    ) {
        
        parent::__construct($context, $backendAuthSession);
        $this->backendSession = $context->getSession();
        $this->customerRepository = $customerRepository;
    }

    /**
     * Customer mass assign group action
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $customersUpdated = 0;
        $customersIds = $this->getRequest()->getParam('customer');
        if (!is_array($customersIds)) {
            $this->messageManager->addError(__('Please select customer(s).'));
        } else {
            foreach ($customersIds as $customerId) {
                $customer = $this->customerRepository->getById($customerId);
                $customer->setGroupId($this->getRequest()->getParam('group'));
                $this->customerRepository->save($customer);
                $customersUpdated++;
            }

            if ($customersUpdated) {
                $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $customersUpdated));
            }
        }
        $this->_redirect('customer/index/index');
        return;
    }
}
