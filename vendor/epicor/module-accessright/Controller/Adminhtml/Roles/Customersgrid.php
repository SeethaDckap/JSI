<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class Customersgrid extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * Customersgrid constructor.
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
     * Customers ajax reload of grid tab
     *
     * @return void
     */
    public function execute()
    {
        $this->loadEntity();
        $customers = $this->getRequest()->getParam('customers');
        $resultLayout = $this->resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->getBlock('customers_grid');
        $block->setSelected($customers);
        
        return $resultLayout;
    }

}
