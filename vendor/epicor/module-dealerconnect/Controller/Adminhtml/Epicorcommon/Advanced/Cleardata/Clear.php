<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicorcommon\Advanced\Cleardata;

class Clear extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Cleardata\Clear
{

    /**
     * @var \Epicor\Common\Helper\Advanced\Cleardata
     */
    protected $commonAdvancedCleardataHelper;
    
     public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Common\Helper\Advanced\Cleardata $commonAdvancedCleardataHelper     
           )
    {
        $this->commonAdvancedCleardataHelper = $commonAdvancedCleardataHelper;
        parent::__construct($context, $backendAuthSession, $commonAdvancedCleardataHelper);
        
    }
    
    public function execute()
    {   
        if ($data = $this->getRequest()->getPost()) {

            $helper = $this->commonAdvancedCleardataHelper;
            /* @var $helper Epicor_Common_Helper_Advanced_Cleardata */


            if (isset($data['locations'])) {
                $this->messageManager->addSuccess(__('Locations cleared from system'));
                $helper->clearLocations();
            }

            if (isset($data['pac'])) {
                $this->messageManager->addSuccess(__('PAC cleared from system'));
                $helper->clearPac();
            }
            
            if (isset($data['products'])) {
                $this->messageManager->addSuccess(__('Products cleared from system'));
                $helper->clearProducts();
            }

            if (isset($data['categories'])) {
                $this->messageManager->addSuccess(__('Categories cleared from system'));
                $helper->clearCategories();
            }

            if (isset($data['erpaccounts'])) {
                $this->messageManager->addSuccess(__('ERP Accounts cleared from system'));
                $helper->clearErpaccounts();
            }

            if (isset($data['customers'])) {
                $this->messageManager->addSuccess(__('Customers cleared from system'));
                $helper->clearCustomers();
            }

            if (isset($data['quotes'])) {
                $this->messageManager->addSuccess(__('Quotes cleared from system'));
                $helper->clearQuotes();
            }

            if (isset($data['orders'])) {
                $this->messageManager->addSuccess(__('Orders cleared from system'));
                $helper->clearOrders();
            }

            if (isset($data['returns'])) {
                $this->messageManager->addSuccess(__('Returns cleared from system'));
                $helper->clearReturns();
            }

            if (isset($data['lists'])) {
                $this->messageManager->addSuccess(__('Lists cleared from system'));
                $helper->clearLists();
            }
        }

        $this->_redirect('*/*/index');
    }

}
