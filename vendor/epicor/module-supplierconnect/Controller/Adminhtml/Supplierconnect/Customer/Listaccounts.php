<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Adminhtml\Supplierconnect\Customer;

class Listaccounts extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
     parent::__construct($context, $backendAuthSession);
     $this->layoutFactory = $layoutFactory;
    }
    
    public function execute()
    {
        if ($this->getRequest()->get('grid')) {
            $output = $this->getLayoutFactory()->create()
                ->createBlock('Epicor\Supplierconnect\Block\Adminhtml\Customer\Supplieraccount\Attribute\Grid')
                ->toHtml();
            $this->getResponse()->setBody($output);
        } else {
            $output = $this->getLayoutFactory()->create()
                ->createBlock('Epicor\Supplierconnect\Block\Adminhtml\Customer\Supplieraccount\Attribute')
                ->toHtml();
            $this->getResponse()->setBody($output);
        }
    }
    
     /**
     * @return \Magento\Framework\View\LayoutFactory
     */
    public function getLayoutFactory()
    {
        return $this->layoutFactory;
    }
}
