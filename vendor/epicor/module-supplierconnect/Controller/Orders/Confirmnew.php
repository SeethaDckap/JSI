<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Controller\Orders;

class Confirmnew extends \Epicor\Supplierconnect\Controller\Orders
{

    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Spoc
     */
    protected $supplierconnectMessageRequestSpoc;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Supplierconnect\Model\Message\Request\Spoc $supplierconnectMessageRequestSpoc
    )
    {
        $this->supplierconnectMessageRequestSpoc = $supplierconnectMessageRequestSpoc;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    /**
     * Confirm / reject new submit action
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();

        if ($data) {

            $message = $this->supplierconnectMessageRequestSpoc;

            $messageTypeCheck = $message->getHelper("supplierconnect/messaging")->getMessageType('SPOC');

            if ($message->isActive() && $messageTypeCheck) {

                if (empty($data['confirmed']) && empty($data['rejected'])) {
                    $this->messageManager->addErrorMessage(__('No POs selected'));
                } else {
                    $message->setPurchaseOrderData($data['purchase_order']);

                    if (isset($data['confirmed']) && !empty($data['confirmed'])) {
                        $message->setConfirmed($data['confirmed']);
                    }

                    if (isset($data['rejected']) && !empty($data['rejected'])) {
                        $message->setRejected($data['rejected']);
                    }

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
                $this->_redirect('*/*/new');
            }
        }
    }

}
