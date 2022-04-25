<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Payments;

class Index extends \Epicor\Customerconnect\Controller\Payments
{
    const FRONTEND_RESOURCE = "Epicor_Customerconnect::customerconnect_account_payments_read";

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cups
     */
    protected $customerconnectMessageRequestCups;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Model\Message\Request\Cups $customerconnectMessageRequestCups,
        \Magento\Framework\Session\Generic $generic
    )
    {
        $this->customerconnectMessageRequestCups = $customerconnectMessageRequestCups;
        $this->generic = $generic;
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
        $cups = $this->customerconnectMessageRequestCups;
        $messageTypeCheck = $cups->getHelper()->getMessageType('CUPS');

        if ($cups->isActive() && $messageTypeCheck) {
            $result = $this->resultPageFactory->create();
            return $result;
        } else {
            $this->messageManager->addErrorMessage("ERROR - Payments Search not available");
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

}
