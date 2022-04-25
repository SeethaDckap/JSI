<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups;

class Edit extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups
{

    public function __construct(
        \Epicor\Dealerconnect\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context, $backendAuthSession);
    }

    /**
     * Group edit action
     *
     * @return void
     */
    public function execute()
    {
        $dealerGrp = $this->loadEntity();
        $resultPage = $this->_resultPageFactory->create();

        $title = __('New Dealer Group');
        if ($dealerGrp->getId()) {
            $title = $dealerGrp->getTitle();
            $title = __('Dealer Group: %1', $title);
        }

        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }

}
