<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Account;

class Index extends \Epicor\Supplierconnect\Controller\Account
{

    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Susd
     */
    protected $supplierconnectMessageRequestSusd;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

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
        \Epicor\Supplierconnect\Model\Message\Request\Susd $supplierconnectMessageRequestSusd,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->supplierconnectMessageRequestSusd = $supplierconnectMessageRequestSusd;
        $this->registry = $registry;
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
        $message = $this->supplierconnectMessageRequestSusd;
        /* @var $message Epicor_Supplierconnect_Model_Message_Request_Susd */
        $helper = $message->getHelper("supplierconnect/messaging");
        $messageTypeCheck = $helper->getMessageType('SUSD');

        if ($message->isActive() && $messageTypeCheck) {
            $message->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
            if ($message->sendMessage()) {
                $this->registry->register('supplier_connect_account_details', $message->getResponse());
            } else {
                $this->messageManager->addErrorMessage('Failed to retrieve Account Details');
            }
        } else {
            $this->messageManager->addErrorMessage('Error - Supplier Connect Dashboard not available');
        }

        $result = $this->resultPageFactory->create();

        $pageMainTitle = $result->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
            $pageMainTitle->setPageTitle('Supplier Connect Dashboard');
        }


        return $result;
    }

}