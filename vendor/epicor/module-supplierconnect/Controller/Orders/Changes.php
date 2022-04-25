<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Orders;

class Changes extends \Epicor\Supplierconnect\Controller\Orders
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_confirm_po_changes_read';
    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Spcs
     */
    protected $supplierconnectMessageRequestSpcs;

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
        \Epicor\Supplierconnect\Model\Message\Request\Spcs $supplierconnectMessageRequestSpcs,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->supplierconnectMessageRequestSpcs = $supplierconnectMessageRequestSpcs;
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
     * Changed purchase order list page
     */
    public function execute()
    {
        $message = $this->supplierconnectMessageRequestSpcs;

        $messageTypeCheck = $message->getHelper("supplierconnect/messaging")->getMessageType('SPCS');

        if ($message->isActive() && $messageTypeCheck) {
            $accessHelper = $this->commonAccessHelper;
            $this->registry->register('orders_editable',
                $accessHelper->customerHasAccess(
                    'Epicor_Supplierconnect',
                    'Orders',
                    'confirmchanges',
                    '',
                    'Access'
                )
            );
            $result = $this->resultPageFactory->create();
        } else {
            $this->messageManager->addErrorMessage(__('Order Search not available'));
            if ($this->messageManager->getMessages()->getItems()) {
                $this->_redirect('customer/account/index');
            }
        }
        return $result;
    }

}
