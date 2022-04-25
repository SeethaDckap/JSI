<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Orders;

class Index extends \Epicor\Dealerconnect\Controller\Orders
{
    const FRONTEND_RESOURCE = 'Dealer_Connect::dealer_orders_read';
    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuos
     */
    protected $customerconnectMessageRequestCuos;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Customerconnect\Model\Message\Request\Cuos $customerconnectMessageRequestCuos
    )
    {
        $this->customerconnectMessageRequestCuos = $customerconnectMessageRequestCuos;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $request,
            $customerconnectHelper,
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
        $cuos = $this->customerconnectMessageRequestCuos;
        $messageTypeCheck = $cuos->getHelper()->getMessageType('CUOS');

        if ($cuos->isActive() && $messageTypeCheck) {
            $resultPage = $this->resultPageFactory->create();
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__("ERROR - Order Search not available"));
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                return $this->resultRedirectFactory->create()->setPath('customer/account/index');
            }
        }
    }

}
