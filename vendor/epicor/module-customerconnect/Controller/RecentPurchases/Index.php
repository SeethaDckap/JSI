<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\RecentPurchases;

class Index extends \Epicor\Customerconnect\Controller\RecentPurchases {

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_recentpurchases_read';
    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cphs
     */
    protected $customerconnectMessageRequestCphs;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Locale\ResolverInterface $localeResolver, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, \Epicor\Customerconnect\Model\Message\Request\Cphs $customerconnectMessageRequestCphs, \Magento\Framework\Session\Generic $generic
    ) {

        $this->customerconnectMessageRequestCphs = $customerconnectMessageRequestCphs;
        $this->generic = $generic;
        parent::__construct(
                $context, $customerSession, $localeResolver, $resultPageFactory, $resultLayoutFactory
        );
    }

    /**
     * Index action 
     */
    public function execute() {
        $cphs = $this->customerconnectMessageRequestCphs;
        $messageTypeCheck = $cphs->getHelper("customerconnect/messaging")->getMessageType('CPHS');

        if ($cphs->isActive() && $messageTypeCheck) {
            if ($this->customerSession->authenticate()) {
                $result = $this->resultPageFactory->create();
                return $result;
            }
        } else {
            $this->messageManager->addErrorMessage(__("ERROR - Recent Purchases search not available"));
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

}
