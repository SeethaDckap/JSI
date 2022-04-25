<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Manage;


class Delete extends  \Epicor\Customerconnect\Controller\Generic
{
    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_group_delete';
    /**
     * @var \Epicor\OrderApproval\Model\Groups
     */
    private $groups;

    /**
     * @var \Epicor\OrderApproval\Model\GroupsRepository
     */
    private $groupsRepository;

    /**
     * Delete constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Epicor\OrderApproval\Model\Groups $groups
     * @param \Epicor\OrderApproval\Model\GroupsRepository $groupsRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\OrderApproval\Model\Groups $groups,
        \Epicor\OrderApproval\Model\GroupsRepository $groupsRepository
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
        $this->groups = $groups;
        $this->groupsRepository = $groupsRepository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $group = $this->groupsRepository->getById($id);
        $groupName = $group->getName();
        if ($this->deleteGroup($id)) {
            $this->messageManager->addSuccessMessage('Deleted group: ' . $groupName);
        }
        $this->_redirect('*/*/');
    }

    /**
     * @param $id
     * @return bool
     */
    private function deleteGroup($id)
    {
        try {
            $this->groupsRepository->deleteById($id);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return false;
        }
        return true;
    }
}
