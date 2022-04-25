<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Arpayments;

class Index extends \Epicor\Customerconnect\Controller\Arpayments
{
    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_ar_payment_payment_read';
    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuad
     */
    protected $customerconnectMessageRequestCuad;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\CacheInterface $cache,
        \Epicor\Customerconnect\Model\Message\Request\Caps $customerconnectMessageRequestCaps,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Access $commonAccessHelper
    )
    {
        $this->customerconnectMessageRequestCaps = $customerconnectMessageRequestCaps;
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $commHelper,
            $customerResourceModelCustomerCollectionFactory,
            $commonAccessGroupCustomerFactory,
            $customerconnectHelper,
            $generic,
            $cache
        );
    }

    /**
     * Index action
     */
    public function execute() {
        $helper = $this->customerconnectHelper;
        $erp_account_number = $helper->getErpAccountNumber();
        $message = $this->customerconnectMessageRequestCaps;
        $error = false;
        $result = array();
        $messageTypeCheck = $message->getHelper()->getMessageType('CAPS');
        if ($message->isActive() && $messageTypeCheck) {
            if ($message->sendMessage()) {
                $result = $this->resultPageFactory->create();
                $this->registry->register('customer_connect_arpayments_details', $message->getResults());
                $accessHelper = $this->commonAccessHelper;
                $this->registry->register('manage_permissions', $accessHelper->customerHasAccess('Epicor_Customerconnect', 'Arpayments', 'index', 'manage_permissions', 'view'));
                $invoices = $this->getRequest()->getParam('invoices');
                $result->getLayout()->getBlock('customer.arpayments.invoices')->setSelected($invoices);
            } else {
                $error = true;
                $this->messageManager->addErrorMessage(__('Failed to retrieve AR Payment Details'));
            }
        } else {
            $error = true;
            $this->messageManager->addErrorMessage(__('AR Payment Details not available'));
        }
        if ($error) {
            $this->_redirect('customer/account/');
        } else {
            return $result;
        }
    }

}