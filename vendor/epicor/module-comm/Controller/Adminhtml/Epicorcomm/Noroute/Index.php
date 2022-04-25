<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Noroute;

class Index extends \Magento\Backend\Controller\Adminhtml\Noroute\Index {

    public function __construct(      
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory   
    ) {
        $this->_publicActions = ['index'];
        parent::__construct($context, $resultPageFactory);  
		
    }
	
}

