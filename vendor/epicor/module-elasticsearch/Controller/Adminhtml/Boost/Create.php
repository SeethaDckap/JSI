<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Controller\Adminhtml\Boost;

use Epicor\Elasticsearch\Controller\Adminhtml\AbstractBoost as BoostController;

/**
 * Boost Adminhtml Create Controller.
 */
class Create extends BoostController
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->createPage();
        $resultPage->setActiveMenu('Epicor_Elasticsearch::boost');
        $resultPage->getConfig()->getTitle()->prepend(__('New Boost'));
        return $resultPage;
    }
}
