<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Controller\Orders;

class Confirmchanges extends \Epicor\Supplierconnect\Controller\Orders
{

    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Spcc
     */
    protected $supplierconnectMessageRequestSpcc;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Supplierconnect\Model\Message\Request\Spcc $supplierconnectMessageRequestSpcc
    )
    {
        $this->supplierconnectMessageRequestSpcc = $supplierconnectMessageRequestSpcc;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    /**
     * Confirm / reject changes submit action
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();

        if ($data) {
            $message = $this->supplierconnectMessageRequestSpcc;
            $messageTypeCheck = $message->getHelper("supplierconnect/messaging")->getMessageType('SPCC');
            if ($message->isActive() && $messageTypeCheck) {
                if (!isset($data['actions']) || empty($data['actions'])) {
                    $this->messageManager->addErrorMessage(__('No PO Lines selected'));
                } else {
                    $message->setActions($data['actions']);

                    if ($message->sendMessage()) {
                        $this->messageManager->addSuccessMessage(__('Purchase Orders processed successfully'));
                    } else {
                        $this->messageManager->addErrorMessage(__('Failed to process Purchase Orders '));
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('Purchase Order updating not available'));
            }

            if ($this->messageManager->getMessages()->getItems()) {
                $this->_redirect('*/*/changes');
            }
        }
    }

}
