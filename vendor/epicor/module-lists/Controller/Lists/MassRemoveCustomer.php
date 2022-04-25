<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Lists;

class MassRemoveCustomer extends \Epicor\Lists\Controller\Lists
{

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
    )
    {
        $this->customerCustomerFactory = $customerCustomerFactory;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $backendJsHelper,
            $commHelper,
            $listsListModelFactory,
            $generic,
            $listsHelper,
            $listsFrontendRestrictedHelper,
            $timezone
        );
    }

    /**
     * Remove Customer Lists
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('listid');
        $customerId = $this->getRequest()->getParam('remove_customer');
        $customer = $this->customerCustomerFactory->create()->load($customerId);
        /* @var $customer \Epicor\Comm\Model\Customer */
        if ($customer->isObjectNew()) {
            $this->messageManager->addErrorMessage(__('Please select a Customer.'));
        } else {
            $explodedIds = explode(',', $ids[0]);
            $customer->removeLists($explodedIds);
            $customer->saveLists();
            $this->messageManager->addSuccessMessage(__('Customer removed from %1 Lists', count($explodedIds)));
        }
        $this->_redirect('*/*/');
    }

}
