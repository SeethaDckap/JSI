<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Groups;

use Epicor\OrderApproval\Model\Groups;
use Magento\Framework\View\Result\Page;
class Edit extends \Epicor\OrderApproval\Controller\Adminhtml\Groups
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var Groups $group */
        $group = $this->loadEntity();

        /** @var Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();

        $title = __('New Group');
        if ($group->getGroupId()) {
            $title = $group->getName();
            $title = __('Group: %1', $title);
        }

        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }

}
