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
class MassDelete extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Customer
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
     * @var \Epicor\Common\Model\CustomerErpaccountFactory
     */
    protected $erpAccountFactory;

    /**
     * MassDelete constructor.
     * @param \Epicor\Comm\Controller\Adminhtml\Context $context
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Epicor\Common\Model\CustomerErpaccountFactory $erpAccountFactory
     */
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        CustomerRepositoryInterface $customerRepository,
        \Epicor\Common\Model\CustomerErpaccountFactory $erpAccountFactory
    ) {
        
        parent::__construct($context, $backendAuthSession);
        $this->backendSession = $context->getSession();
        $this->customerRepository = $customerRepository;
        $this->erpAccountFactory = $erpAccountFactory;
    }

    /**
     * Customer mass assign group action
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $customersDeleted = 0;
        $multipleErpCounts = 0;
        $customersIds = $this->getRequest()->getParam('customer');
        if (!is_array($customersIds)) {
            $this->messageManager->addError(__('Please select customer(s).'));
        } else {
            $customersIds = array_unique($customersIds);
            foreach ($customersIds as $customerId) {
                $erpCount = $this->erpAccountFactory->create()->setData(['customer_id' => $customerId])->getErpAcctCounts();
                if(!empty($erpCount) && count($erpCount) > 1){
                    $multipleErpCounts++;
                    continue;
                }
                $this->customerRepository->deleteById($customerId);
                $customersDeleted++;
            }

            if($multipleErpCounts > 0){
                $this->messageManager->addErrorMessage(
                    __('Total of %1 record(s) could not be updated. Customer(s) selected are mapped to more than 1 ERP Account and this action is not permitted.', $multipleErpCounts)
                );
            }

            if ($customersDeleted) {
                $this->messageManager->addSuccess(__('A total of %1 record(s) were deleted.', $customersDeleted));
            }
        }
        $this->_redirect('customer/index/index');
        return;    
        
    }
}
