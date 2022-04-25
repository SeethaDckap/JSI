<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Restrictionsgrid extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    public function __construct(
      \Epicor\Lists\Controller\Adminhtml\Context $context,
      \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
         parent::__construct($context, $backendAuthSession);
    }
    /**
     * Addresses ajax reload of grid tab
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('restrictions_grid');
        $this->_view->renderLayout();
    }

}
