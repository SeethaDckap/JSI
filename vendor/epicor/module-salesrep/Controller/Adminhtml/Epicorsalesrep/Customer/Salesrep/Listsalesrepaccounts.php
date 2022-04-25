<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep;

class Listsalesrepaccounts extends \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep
{

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    public function __construct(\Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Context $context)
    {
        $this->resultLayoutFactory = $context->getResultLayoutFactory();
        parent::__construct($context);
    }

    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        if ($this->getRequest()->getParam('grid')) {
            $this->getResponse()->setBody(
                $resultLayout->getLayout()->createBlock('Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Popup\Grid')->toHtml()
            );
        } else {
            $this->getResponse()->setBody(
                $resultLayout->getLayout()->createBlock('Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Popup')->toHtml()
            );
        }
    }

}
