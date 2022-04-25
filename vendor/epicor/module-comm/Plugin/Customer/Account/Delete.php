<?php
/**
 *
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Customer\Account;

use Magento\Framework\Controller\ResultFactory;

class Delete
{
    /**
     * @var \Epicor\Common\Model\CustomerErpaccountFactory
     */
    protected $erpAccountFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    public function __construct(
        \Epicor\Common\Model\CustomerErpaccountFactory $erpAccountFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ResultFactory $resultFactory
    )
    {
        $this->erpAccountFactory = $erpAccountFactory;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @param \Magento\Customer\Controller\Adminhtml\Index\Delete $subject
     * @param \Closure $proceed
     * @return \Magento\Backend\Model\View\Result\Redirect|mixed
     */
    public function aroundExecute(
        \Magento\Customer\Controller\Adminhtml\Index\Delete $subject,
        \Closure $proceed
    )
    {
        $customerId = (int)$subject->getRequest()->getParam('id');
        if (!empty($customerId)) {
            $erpCount = $this->erpAccountFactory->create()->setData(['customer_id' => $customerId])->getErpAcctCounts();
            if (!empty($erpCount) && count($erpCount) > 1) {
                $this->messageManager->addErrorMessage(
                    __('This action is not permitted as Customer is mapped to more than 1 ERP Account.')
                );
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath(
                    'customer/*/edit',
                    ['id' => $customerId, '_current' => true]
                );
                return $resultRedirect;
            }
        }
        return $proceed();
    }
}
