<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups;

class Index extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups
{
    public function __construct(
        \Epicor\Dealerconnect\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {  
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Dealer Groups'));

        return $resultPage;
    }

}
