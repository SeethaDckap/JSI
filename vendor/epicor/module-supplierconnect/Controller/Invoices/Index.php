<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Invoices;

class Index extends \Epicor\Supplierconnect\Controller\Invoices
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_invoices_read';
    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Suis
     */
    protected $supplierconnectMessageRequestSuis;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Supplierconnect\Model\Message\Request\Suis $supplierconnectMessageRequestSuis
    ) {
        $this->supplierconnectMessageRequestSuis = $supplierconnectMessageRequestSuis;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $generic,
            $supplierconnectHelper,
            $request,
            $commHelper,
            $registry
        );
    }

    /**
     * Index action 
     */
    public function execute()
    {
        $helper = $this->supplierconnectHelper;
        /* @var $helper \Epicor\Supplierconnect\Helper\Data */

        $suis = $this->supplierconnectMessageRequestSuis;
        $messageTypeCheck = $helper->getMessageType('SUIS');

        if ($suis->isActive() && $messageTypeCheck) {
            return $this->resultPageFactory->create();
        } else {
            $this->messageManager->addErrorMessage('Invoice Search not available');
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

}