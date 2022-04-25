<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Controller\Pickup;

class Selectgrid extends \Epicor\BranchPickup\Controller\Pickup
{
    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    protected $layoutFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context, $branchPickupHelper, $customerSession);
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Sends the Branchp Pickup select grid action
     *
     * @return void
     */
    public function execute()
    {
        //$this->loadEntity();
        $this->_view->loadLayout();
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock('Epicor\BranchPickup\Block\Pickup\Select\Grid')->toHtml()
        );
    }

}
