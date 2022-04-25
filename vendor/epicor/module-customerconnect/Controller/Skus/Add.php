<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Skus;

use Epicor\Customerconnect\Model\Skus\CpnuManagement;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Add
 * @package Epicor\Customerconnect\Controller\Skus
 */
class Add extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var CpnuManagement
     */
    private $cpnuManagement;

    /**
     * Add constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param CpnuManagement $cpnuManagement
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        CpnuManagement $cpnuManagement
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->cpnuManagement = $cpnuManagement;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->isAddAllowed()) {
            $this->messageManager->addErrorMessage('You don\'t have permission to access the page.');
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
        }
        return $this->pageFactory->create();
    }

    /**
     * @return bool
     */
    private function isAddAllowed()
    {
        return ($this->cpnuManagement->isEditable() &&
            $this->cpnuManagement->isAccessAllowed(CpnuManagement::FRONTEND_RESOURCE_ACCOUNT_SKU_ADD));
    }
}
