<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Quickstart;

class Index extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Quickstart
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        $this->_storeId = $this->storeManager->getStore()->getId();

        $this->_registry->register('quickstartData', \Epicor\Common\Helper\Quickstart::$CONFIG_FIELDS);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Common::epicor');
        $resultPage->getConfig()->getTitle()->prepend(__('Quick Start'));

        return $resultPage;
    }

}
