<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Addbycsv extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Displays form to upload CSV file
     *
     * @return void
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Lists CSV Upload'));

        return $resultPage;
    }
}
