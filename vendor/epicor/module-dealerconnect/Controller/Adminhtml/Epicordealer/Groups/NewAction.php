<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups;

class NewAction extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups
{

    public function __construct(
        \Epicor\Dealerconnect\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * new Dealer Group action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }

}
