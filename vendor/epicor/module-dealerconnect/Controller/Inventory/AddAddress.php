<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Inventory;

class AddAddress extends \Magento\Framework\App\Action\Action
{

	protected $_pageFactory;
        
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
            $this->_view->loadLayout();
            $this->getResponse()->setBody($this->_view->getLayout()
                                ->createBlock('Epicor\Dealerconnect\Block\Portal\Inventory\Details\UpdateAddress')
                                ->setTemplate('Epicor_Dealerconnect::epicor/dealerconnect/deid/updateaddress.phtml')->toHtml());
	}
    
   

}
