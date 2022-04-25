<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\QuickOrderPad\Controller\Form;

class Index extends \Epicor\QuickOrderPad\Controller\Form
{

    /**
     * @var \Epicor\Lists\Helper\Frontend\Quickorderpad
     */
    protected $listsQopHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Lists\Helper\Frontend\Quickorderpad $listsQopHelper
    )
    {
        $this->listsQopHelper = $listsQopHelper;
        parent::__construct(
            $context,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }


    /**
     * Default Page
     */
    public function execute()
    {
        if ($this->listsQopHelper->listsEnabled() && $this->listsQopHelper->getSessionList()) {
            return $this->_redirect('quickorderpad/form/results');
        } else {
            $result = $this->resultPageFactory->create();

            return $result;
        }
    }

}
