<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Parts;

class Index extends \Epicor\Supplierconnect\Controller\Generic
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_parts_read';
    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Spls
     */
    protected $supplierconnectMessageRequestSpls;

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
        \Epicor\Supplierconnect\Model\Message\Request\Spls $supplierconnectMessageRequestSpls,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->supplierconnectMessageRequestSpls = $supplierconnectMessageRequestSpls;
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
        $message = $this->supplierconnectMessageRequestSpls;
        $helper = $message->getHelper("supplierconnect/messaging");
        $messageTypeCheck = $helper->getMessageType('SPLS');
        if ($message->isActive() && $messageTypeCheck) {
            $resultPage = $this->resultPageFactory->create();
            return $resultPage; 
        } else {
            $this->generic->addError('Parts Search not available');
            if ($this->generic->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

    }
