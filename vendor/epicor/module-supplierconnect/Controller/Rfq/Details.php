<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Rfq;

class Details extends \Epicor\Supplierconnect\Controller\Rfq
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_rfqs_details';
    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Surd
     */
    protected $supplierconnectMessageRequestSurd;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

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
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Epicor\Supplierconnect\Model\Message\Request\Surd $supplierconnectMessageRequestSurd
    )
    {

        $this->supplierconnectMessageRequestSurd = $supplierconnectMessageRequestSurd;
        $this->encryptor = $encryptor;
        $this->urlDecoder = $urlDecoder;
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
        /* @var $helper Epicor\Comm\Helper\Messaging */
        $rfq = $this->urlDecoder->decode($this->request->getParam('rfq'));
        $rfq_requested = unserialize($this->encryptor->decrypt($rfq));
        $erp_account_number = $this->commHelper->getSupplierAccountNumber();

        if (
            count($rfq_requested) == 3 &&
            $rfq_requested[0] == $erp_account_number &&
            !empty($rfq_requested[1]) &&
            !empty($rfq_requested[2])
        ) {
            $surd = $this->supplierconnectMessageRequestSurd;
            /* @var $surd Epicor\Supplierconnect\Model\Message\Request\Surd */

            if ($surd->isActive() && $helper->getMessageType('SURD')) {
                $surd->setRfqNumber($rfq_requested[1])
                    ->setLine($rfq_requested[2])
                    ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));

                if ($surd->sendMessage()) {
                    $rfqMsg = $surd->getResults();
                    if ($rfqMsg) {
                        $rfq = $rfqMsg->getRfq();
                        if ($rfq) {
                            $this->registry->register('supplier_connect_rfq_details', $rfq);
                            $allowOverride = ($rfq->getAllowConversionOverride() == 'Y') ? true : false;
                            $this->registry->register('allow_conversion_override', $allowOverride);
                            $accessHelper = $this->commonAccessHelper;
                            /* @var $helper Epicor\Common\Helper\Access */
                            $editable = $accessHelper->customerHasAccess('Epicor_Supplierconnect', 'Rfq', 'update', '', 'Access');

                            if ($rfq->getStatus() == 'C') {
                                $editable = false;
                            }

                            $this->registry->register('rfq_editable', $editable);

                            $resultPage = $this->resultPageFactory->create();
                            $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
                            if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
                                $pageMainTitle->setPageTitle(__('RFQ : %1', $rfq->getRfqNumber()));
                            }

                            return $resultPage;
                        } else {
                            $this->messageManager->addErrorMessage(__('Failed to retrieve RFQ Details from message'));
                        }
                    } else {
                        $this->messageManager->addErrorMessage(__('Failed to retrieve RFQ Details from message'));
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('Failed to retrieve RFQ Details'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('RFQ Details not available'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid RFQ Number'));
        }

        if ($this->messageManager->getMessages()->getItems()) {
            $this->_redirect('*/*/index');
        }
    }
}