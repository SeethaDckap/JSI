<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Payments;

class Index extends \Epicor\Supplierconnect\Controller\Payments
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_payments_read';
    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Sups
     */
    protected $supplierconnectMessageRequestSups;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Supplierconnect\Model\Message\Request\Sups $supplierconnectMessageRequestSups
    ) {
        $this->supplierconnectMessageRequestSups = $supplierconnectMessageRequestSups;
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
        $message = $this->supplierconnectMessageRequestSups;
        $messageTypeCheck = $message->getHelper("supplierconnect/messaging")->getMessageType('SUPS');

        if ($message->isActive() && $messageTypeCheck) {
            return $this->resultPageFactory->create();
        } else {
            $this->messageManager->addErrorMessage('Payment Search not available');
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

}
