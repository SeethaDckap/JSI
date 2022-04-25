<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Controller\Pickup;

class Pickupsearchgrid extends \Epicor\BranchPickup\Controller\Pickup
{
    protected $_gridFactory;
    
    protected $_session;

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
    
    public function execute()
    {
        $block = $this->_view->getLayout()->createBlock('Epicor\BranchPickup\Block\Pickupsearch\Select');
        $this->getResponse()->setBody($block->toHtml());
    }
    
}  