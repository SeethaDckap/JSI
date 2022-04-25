<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Controller\Form;

class Listselect extends \Epicor\QuickOrderPad\Controller\Form
{

    /**
     * @var \Epicor\Lists\Helper\Frontend
     */
    protected $listsFrontendHelper;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Lists\Helper\Frontend $listsFrontendHelper
    ) {
        $this->listsFrontendHelper = $listsFrontendHelper;
        parent::__construct(
            $context,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }


/**
     * Sets the list selected and returns to the results page
     *
     * @return void
     */
    public function execute()
    {
        $listId = $this->getRequest()->getParam('list_id');
        
        if ($this->listsFrontendHelper->getValidListById($listId)) {
            $this->listsFrontendHelper->setSessionList($listId);
        } else {
            $this->listsFrontendHelper->setSessionList(-1);
        }

        $this->_redirect('quickorderpad/form/results');
    }

}
