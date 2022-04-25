<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups;

class Erpaccountsgrid extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicordealer\Groups
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    public function __construct(
        \Epicor\Dealerconnect\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->resultLayoutFactory = $context->getResultLayoutFactory();
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Dealer Accounts ajax reload of grid tab
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
