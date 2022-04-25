<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Controller\Adminhtml\Boost;

use Epicor\Elasticsearch\Controller\Adminhtml\AbstractBoost as BoostController;

/**
 * Boost Adminhtml Index controller.
 *
 */
class Index extends BoostController
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Boost List'));
        $resultPage->addBreadcrumb(__('Boosts'), __('Boosts'));
        return $resultPage;
    }
}
