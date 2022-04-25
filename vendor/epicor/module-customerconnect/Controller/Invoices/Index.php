<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Invoices;

class Index extends \Epicor\Customerconnect\Controller\Invoices
{

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuis
     */
    protected $customerconnectMessageRequestCuis;

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_invoices_read';

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Model\Message\Request\Cuid $customerconnectMessageRequestCuid,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Customerconnect\Model\Message\Request\Cuis $customerconnectMessageRequestCuis
    )
    {
        $this->customerconnectMessageRequestCuis = $customerconnectMessageRequestCuis;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $customerconnectHelper,
            $request,
            $customerconnectMessageRequestCuid,
            $registry,
            $generic,
            $urlDecoder,
            $encryptor
        );
    }

    /**
     * Index action
     */
    public function execute()
    {
        $cuis = $this->customerconnectMessageRequestCuis;
        $messageTypeCheck = $cuis->getHelper()->getMessageType('CUIS');

        if ($cuis->isActive() && $messageTypeCheck) {
            $result = $this->resultPageFactory->create();
            return $result;
        } else {
            $this->messageManager->addErrorMessage(__("ERROR - Invoices Search not available"));
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

}
