<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Esdm\Controller\Savedcards;


class Index extends \Epicor\AccessRight\Controller\Action
{
    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_esdm_edit';

    protected $_gridFactory;
    
    protected $_session;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Esdm\Model\TokenFactory $gridFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->_gridFactory     = $gridFactory;
        $this->_session         = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
    
}