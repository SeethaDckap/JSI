<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class Erpaccountsgrid extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * Erpaccountsgrid constructor.
     *
     * @param \Epicor\AccessRight\Controller\Adminhtml\Context $context
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     */
    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->resultLayoutFactory = $context->getResultLayoutFactory();
        parent::__construct($context, $backendAuthSession);
    }

    /**
     * ERP Accounts grid load by ajax
     *
     * @return void
     */
    public function execute()
    {
        $this->loadEntity();
        $erpaccounts = $this->getRequest()->getParam('erpaccounts');
        $resultLayout = $this->resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->getBlock('erpaccounts_grid');
        $block->setSelected($erpaccounts);

        return $resultLayout;
    }
}
