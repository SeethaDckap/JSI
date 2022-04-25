<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Documentation;

class Index extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Documentation
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
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Common::epicor');
        $resultPage->getConfig()->getTitle()->prepend(__('Documentation'));

        return $resultPage;
    }

}
