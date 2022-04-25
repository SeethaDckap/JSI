<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Edit extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context, $backendAuthSession);
    }

    /**
     * List edit action
     *
     * @return void
     */
    public function execute()
    {
        $list=$this->loadEntity();
        $resultPage = $this->_resultPageFactory->create();
        
        $title = __('New List');
        if ($list->getId()) {
            $title = $list->getTitle();
            //M1 > M2 Translation Begin (Rule 55)
            //return __('List: %s', $title);
            $title = __('List: %1', $title);
            //M1 > M2 Translation End
        }
        
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }

}
