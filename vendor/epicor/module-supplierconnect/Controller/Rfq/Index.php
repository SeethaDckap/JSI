<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Rfq;

class Index extends \Epicor\Supplierconnect\Controller\Rfq
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_rfqs_read';
    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Surs
     */
    protected $supplierconnectMessageRequestSurs;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Supplierconnect\Model\Message\Request\Surs $supplierconnectMessageRequestSurs
    ) {
        $this->supplierconnectMessageRequestSurs = $supplierconnectMessageRequestSurs;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $commMessagingHelper,
            $generic,
            $request,
            $commHelper,
            $registry,
            $commonAccessHelper
        );
    }
    public function execute()
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper \Epicor\Comm\Helper\Messaging */

        $surs = $this->supplierconnectMessageRequestSurs;
        /* @var $surs \Epicor\Supplierconnect\Model\Message\Request\Surs */
        if ($surs->isActive() && $helper->getMessageType('SURS')) {
            return $this->resultPageFactory->create();
        } else {
            $this->messageManager->addErrorMessage('ERROR - RFQ Search not available');
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

}
