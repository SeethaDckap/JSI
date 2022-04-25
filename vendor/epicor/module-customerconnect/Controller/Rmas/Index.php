<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rmas;

class Index extends \Epicor\Customerconnect\Controller\Rmas
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_rma_read';

    /**
     * Index action
     */

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Curs
     */
    protected $customerconnectMessageRequestCurs;

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
        \Epicor\Customerconnect\Model\Message\Request\Curs $customerconnectMessageRequestCurs,
        \Magento\Framework\Session\Generic $generic
    )
    {
        $this->customerconnectMessageRequestCurs = $customerconnectMessageRequestCurs;
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
        $curs = $this->customerconnectMessageRequestCurs;
        $messageTypeCheck = $curs->getHelper()->getMessageType('CURS');

        if ($curs->isActive() && $messageTypeCheck) {
            return $this->resultPageFactory->create();
        } else {
            $this->messageManager->addErrorMessage("ERROR - RMA Search not available");
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

}
