<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Arpayments;

class Grid extends \Epicor\Customerconnect\Controller\Arpayments
{

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
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    )
    {
        $this->customerconnectMessageRequestCaps = $customerconnectMessageRequestCaps;
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->layoutFactory = $layoutFactory;
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
    public function execute()
    {
        $helper = $this->customerconnectHelper;
        $erp_account_number = $helper->getErpAccountNumber();
        $message = $this->customerconnectMessageRequestCaps;
        $error = false;
        $messageTypeCheck = $message->getHelper()->getMessageType('CAPS');
        if ($message->isActive() && $messageTypeCheck) {
            $invoices = $this->getRequest()->getPost('invoices', null);
            $this->_view->loadLayout()->getLayout()
                        ->getBlock('customer.arpayments.invoices')
                        ->setSelected($invoices);
            $this->_view->renderLayout();            
        } else {
            $error = true;
            $this->messageManager->addErrorMessage(__('Account Details not available'));
        }        

    }
    
    /**
     * @return \Magento\Framework\View\LayoutFactory
     */
    public function getLayoutFactory() {
        return $this->layoutFactory;
    }    

}
