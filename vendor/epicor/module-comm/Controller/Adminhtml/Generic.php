<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml;


use Magento\Backend\App\Action;

abstract class Generic extends \Magento\Backend\App\Action
{

    protected $_aclId = null;
    protected $_translatePath;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;


    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->backendAuthSession = $backendAuthSession;
        $this->_resultPageFactory = $context->getResultPageFactory();
        $this->_registry = $context->getRegistry();
        $this->_resultLayoutFactory = $context->getResultLayoutFactory();
        parent::__construct(
            $context    
        );
    }


    protected function _isAllowed()
    {
        if ($this->_aclId) {
            return $this->backendAuthSession->isAllowed($this->_aclId);
        } else {
            return true;
        }
    }

    protected function setAlcId($aclId)
    {
        $this->_aclId = $aclId;
    }

}
