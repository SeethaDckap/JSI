<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Controller\Manage;

class Rules extends \Epicor\Lists\Controller\Lists
{
    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $this->loadEntity();
        $layout = $this->_view->loadLayout();
        $rulesBlock = $layout->getLayout()->getBlock('group_rules');
        $rulesBlock->setGroupId($this->getRequest()->getParam('id'));

        $this->_view->renderLayout();
    }
}
