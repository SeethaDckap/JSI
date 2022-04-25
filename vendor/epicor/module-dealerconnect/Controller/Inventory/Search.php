<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Inventory;

class Search extends \Epicor\AccessRight\Controller\Action
{
    const FRONTEND_RESOURCE = 'Dealer_Connect::dealer_inventory_read';
    /**
     * @var \Epicor\Dealerconnect\Model\Message\Request\Cuos
     */
    protected $dealerconnectMessageRequestDeis;
    
    
    protected $resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Dealerconnect\Model\Message\Request\Deis $dealerconnectMessageRequestDeis
    )
    {
        $this->dealerconnectMessageRequestDeis = $dealerconnectMessageRequestDeis;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     */
    public function execute()
    {
        $deis = $this->dealerconnectMessageRequestDeis;
        $messageTypeCheck = $deis->getHelper()->getMessageType('DEIS');

        if ($deis->isActive() && $messageTypeCheck) {
            $resultPage = $this->resultPageFactory->create();
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__("ERROR - Inventory Search not available"));
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                return $this->resultRedirectFactory->create()->setPath('customer/account/index');
            }
        }
    }

}
