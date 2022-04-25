<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Nonerpproduct;

class FormPopup extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct(
            $context
        );
    }

    /**
     * Shows the nonerpproduct address form
     * return html
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $resultPage = $this->resultPageFactory->create();
       //    $this->getResponse()->setBody($this->_view->getLayout()
        $this->getResponse()->setBody($resultPage->getLayout()
                            ->createBlock('Magento\Customer\Block\Address\Edit', 'capture.customer.info')
                            ->setTemplate('Epicor_Common::epicor_common/checkout/onepage/capture_customer_info.phtml')->toHtml());
    }
    
}