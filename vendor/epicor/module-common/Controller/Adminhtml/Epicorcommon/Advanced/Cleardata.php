<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced;


/**
 * Common Clear Data Controller
 *
 * This controls the ability to clear data from the system
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 * 
 */
abstract class Cleardata extends \Epicor\Comm\Controller\Adminhtml\Generic
{
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
        )
    {
        parent::__construct($context, $backendAuthSession);
    }
   
    protected function _initPage()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Comm::advanced');
        $resultPage->getConfig()->getTitle()->prepend(__('Clear Data'));
        
        return $resultPage;
    }
    
}
