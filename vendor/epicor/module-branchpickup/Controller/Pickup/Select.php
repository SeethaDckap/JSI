<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Controller\Pickup;

class Select extends \Epicor\BranchPickup\Controller\Pickup
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
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $branchPickupHelper, $customerSession);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Sends the Branchp Pickup grid Selector
     *
     * @return void
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Branch Pickup Select'));

        return $resultPage;
    }
}
