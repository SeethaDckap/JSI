<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Shipments;

class Index extends \Epicor\Customerconnect\Controller\Shipments
{

    const FRONTEND_RESOURCE = "Epicor_Customerconnect::customerconnect_account_shipments_read";

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuss
     */
    protected $customerconnectMessageRequestCuss;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Model\Message\Request\Cusd $customerconnectMessageRequestCusd,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Epicor\Customerconnect\Model\Message\Request\Cuss $customerconnectMessageRequestCuss
    ) {
        $this->customerconnectMessageRequestCuss = $customerconnectMessageRequestCuss;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $customerconnectHelper,
            $request,
            $customerconnectMessageRequestCusd,
            $registry,
            $generic,
            $encryptor,
            $urlDecoder
        );
    }
    /**
     * Index action 
     */
    public function execute()
    {
        $cuss = $this->customerconnectMessageRequestCuss;
        $messageTypeCheck = $cuss->getHelper()->getMessageType('CUSS');

        if ($cuss->isActive() && $messageTypeCheck) {
            return $this->resultPageFactory->create();
        } else {
            $this->messageManager->addErrorMessage("ERROR - Shipment Search not available");
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

    }
