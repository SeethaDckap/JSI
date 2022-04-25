<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Controller\Manage;

class MassDelete  extends \Epicor\Customerconnect\Controller\Generic
{
    /**
     * @var \Epicor\OrderApproval\Api\GroupsRepositoryInterface
     */
    private $groupRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\OrderApproval\Api\GroupsRepositoryInterface $groupRepository
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
        $this->groupRepository = $groupRepository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $groupIds = $this->getGroupIds();

        $successDeletions = [];
        foreach ($groupIds as $id) {
            if ($id === 'disabled') {
                continue;
            }
            try {
                if ($this->groupRepository->deleteById($id)) {
                    $successDeletions[] = $id;
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $deletionCount = count($successDeletions);
        if (!empty($successDeletions)) {
            $successList = implode(', ', $successDeletions);
            $this->messageManager->addSuccessMessage(
                __($deletionCount . ' Groups deleted. ' . "Group Reference Code: (" . $successList . ")")
            );
        }

        $this->_redirect('*/*/');
    }

    /**
     * @return false|string[]
     */
    private function getGroupIds()
    {
        $groupIds = $this->getRequest()->getParam('groupid');

        return explode(',', $groupIds);
    }
}
