<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Dashboard;

class Managesave extends \Epicor\Dealerconnect\Controller\Dashboard
{

    /**
     * @var \Epicor\Common\Model\ManagedashboardFactory
     */
    protected $managedashboardFactory;

    /**
     * @var \Epicor\Dealerconnect\Model\Dashboard
     */
    protected $dealerDashboard;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Model\ManagedashboardFactory $managedashboardFactory,
        \Epicor\Dealerconnect\Model\Dashboard $dealerDashboard
    )
    {
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->managedashboardFactory = $managedashboardFactory;
        $this->dealerDashboard = $dealerDashboard;

        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    /**
     * Index action
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Manage Dashbpard Not Saved'));
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        }
        $customer_id = $this->customerSession->getCustomer()->getId();
        $account_id = $this->customerSession->getCustomer()->getEccErpaccountId();
        $post = $this->getRequest()->getPost();
        $dealerGridFilters = $this->dealerDashboard->getDealerGridFilters();
        $postdata = [];
        foreach ($post as $key => $data) {
            $data['customer_id'] = $customer_id;
            $data['account_id'] = $account_id;
            $data['message_type'] = \Epicor\Dealerconnect\Model\Dashboard::ACCOUNT_TYPE;
            $data['code'] = $key;
            if (isset($data['filters'])) {
                if (isset($dealerGridFilters[$key]['dealer'])) {
                    $data['filters'] = json_encode(['dealer' => $data['filters']]);
                }
            } else if(isset($data['statuses'])) {
                $data['filters'] = json_encode(['statuses' => $data['statuses']]);
            } else {
                $data['filters'] = 0;
            }
            $data['status'] = isset($data['status']) ? $data['status'] : 0;
            $data['date_range'] = isset($data['date_range']) ? $data['date_range'] : 0;
            $data['grid_count'] = isset($data['grid_count']) ? $data['grid_count'] : 0;
            $postdata[] = $data;

        }
        $this->managedashboardFactory->create()->saveRel($postdata);
        $this->messageManager->addSuccessMessage(__('Manage Dashbpard Saved Successfully'));
        $this->_view->loadLayout();
        $this->_view->renderLayout();

    }

}
