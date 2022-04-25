<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Dashboard;

class Index extends \Epicor\Dealerconnect\Controller\Dashboard
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Dealerconnect\Model\Dashboard
     */
    protected $dashboard;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Model\Dashboard $dashboard
    ) {
        $this->registry = $registry;
        $this->dashboard = $dashboard;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }


    /**
     * @return bool
     */
    protected function _isAccessAllowed($code)
    {
        return $this->_accessauthorization->isAllowed($code);
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if ($request->isDispatched()
            && $request->getActionName() !== 'denied'
            && $this->isDashboardAccessNotAllowed()
        ) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()->unsetElement('content');
            $resultPage->getLayout()->getBlock('page.main.title')->setTemplate('Epicor_AccessRight::access_denied.phtml');
            return $resultPage;

        }

        return parent::dispatch($request);
    }
    /**
     * Index action 
     */
    public function execute()
    {
        $dashboardConfiguration = $this->dashboard->getDashboardConfiguration();
        $dashboardConfiguration = array_filter($dashboardConfiguration, function($data){
            return $data['allowed'];
        });
        $this->registry->register('dashboard_configuration', $dashboardConfiguration);
        $result = $this->resultPageFactory->create();
        return $result;
    }

    public function isDashboardAccessNotAllowed()
    {
        return false;
        return !$this->_isAccessAllowed('Epicor_Customerconnect::customerconnect_account_information_billing_read')
            && !$this->_isAccessAllowed('Epicor_Customerconnect::customerconnect_account_information_information_read')
            && !$this->_isAccessAllowed('Epicor_Customerconnect::customerconnect_account_orders_read')
            && !$this->_isAccessAllowed('Epicor_Customerconnect::customerconnect_account_invoices_read');
    }

}
