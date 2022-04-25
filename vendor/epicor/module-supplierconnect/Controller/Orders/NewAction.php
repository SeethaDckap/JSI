<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Orders;

class NewAction extends \Epicor\Supplierconnect\Controller\Orders
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_confirm_new_po_read';
    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Spos
     */
    protected $supplierconnectMessageRequestSpos;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Supplierconnect\Model\Message\Request\Spos $supplierconnectMessageRequestSpos,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->supplierconnectMessageRequestSpos = $supplierconnectMessageRequestSpos;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
    /**
     * New purchase order list page
     */
    public function execute()
    {
        $message = $this->supplierconnectMessageRequestSpos;
        $messageTypeCheck = $message->getHelper("supplierconnect/messaging")->getMessageType('SPOS');
        $resultPage = $this->resultPageFactory->create();
        if ($message->isActive() && $messageTypeCheck) {
            $accessHelper = $this->commonAccessHelper;
            $this->registry->register('orders_editable', $accessHelper->customerHasAccess('Epicor_Supplierconnect', 'Orders', 'confirmnew', '', 'Access'));

        } else {
            $this->messageManager->addErrorMessage(__('Order Search not available'));
            if ($this->messageManager->getMessages()->getItems()) {
                $this->_redirect('customer/account/index');
            }
        }
        return $resultPage;
    }

    }
