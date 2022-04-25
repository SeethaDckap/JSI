<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class CartPopup extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    protected $resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,        
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct(
            $context
        );
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Shows the cart popup, If the item are not available for the selected address
     * return html
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $this->getResponse()->setBody($resultPage->getLayout()->createBlock('Epicor\BranchPickup\Block\Cart\Sidebar')->setTemplate('Epicor_Lists::epicor/lists/checkout/sidebar.phtml')->toHtml());
    }

    }
