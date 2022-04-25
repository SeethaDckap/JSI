<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Contracts;

class Index extends \Epicor\Customerconnect\Controller\Contracts
{
    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_contracts_read';
    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuis
     */
    protected $customerconnectMessageRequestCccs;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Locale\ResolverInterface $localeResolver, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, \Epicor\Customerconnect\Helper\Data $customerconnectHelper, \Magento\Framework\App\Request\Http $request, \Epicor\Customerconnect\Model\Message\Request\Cccs $customerconnectMessageRequestCccs, \Magento\Framework\Registry $registry, \Magento\Framework\Session\Generic $generic, \Magento\Framework\Url\DecoderInterface $urlDecoder, \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->customerconnectMessageRequestCccs = $customerconnectMessageRequestCccs;
        $this->generic = $generic;
        parent::__construct(
                $context, $customerSession, $localeResolver, $resultPageFactory, $resultLayoutFactory, $customerconnectHelper, $request, $customerconnectMessageRequestCccs, $registry, $generic, $urlDecoder, $encryptor
        );
    }

    /**
     * Index action 
     */
    public function execute() {
        $cccs = $this->customerconnectMessageRequestCccs;
        $messageTypeCheck = $cccs->getHelper("customerconnect/messaging")->getMessageType('CCCS');

        if ($cccs->isActive() && $messageTypeCheck) {
            $result = $this->resultPageFactory->create();
            return $result;
        } else {
            $this->generic->addError(__("ERROR - Contracts Search not available"));
            if ($this->generic->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

}
