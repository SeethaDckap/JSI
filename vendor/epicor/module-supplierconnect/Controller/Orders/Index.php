<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Orders;

class Index extends \Epicor\Supplierconnect\Controller\Orders
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_orders_read';
    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Spos
     */
    protected $supplierconnectMessageRequestSpos;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Supplierconnect\Model\Message\Request\Spos $supplierconnectMessageRequestSpos
    ) {
        $this->supplierconnectMessageRequestSpos = $supplierconnectMessageRequestSpos;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
    /**
     * Index action - purchase order list page
     */
    public function execute()
    {
        $message = $this->supplierconnectMessageRequestSpos;
        $messageTypeCheck = $message->getHelper("supplierconnect/messaging")->getMessageType('SPOS');

        if ($message->isActive() && $messageTypeCheck) {
        } else {
            $this->messageManager->addErrorMessage(__('Order Search not available'));
            if ($this->messageManager->getMessages()->getItems()) {
                $this->_redirect('customer/account/index');
            }
        }

        $result = $this->resultPageFactory->create();
        return $result;
    }

    }
