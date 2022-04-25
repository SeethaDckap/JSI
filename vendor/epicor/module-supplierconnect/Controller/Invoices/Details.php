<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Invoices;

class Details extends \Epicor\Supplierconnect\Controller\Invoices
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_invoices_details';
    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Suid
     */
    protected $supplierconnectMessageRequestSuid;

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
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Supplierconnect\Model\Message\Request\Suid $supplierconnectMessageRequestSuid,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    ) {
        $this->supplierconnectMessageRequestSuid = $supplierconnectMessageRequestSuid;
        $this->encryptor = $encryptor;
        $this->urlDecoder = $urlDecoder;

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

    public function execute()
    {
        $helper = $this->supplierconnectHelper;
        /* @var $helper Epicor\Supplierconnect\Helper\Data */

        $invoice = $this->urlDecoder->decode($this->request->getParam('invoice'));
        $invoice_requested = unserialize($this->encryptor->decrypt($invoice));
        $erp_account_number = $this->commHelper->getSupplierAccountNumber();

        if (
            count($invoice_requested) == 2 &&
            $invoice_requested[0] == $erp_account_number &&
            !empty($invoice_requested[1])
        ) {
            $message = $this->supplierconnectMessageRequestSuid;
            $messageTypeCheck = $message->getHelper("supplierconnect/messaging")->getMessageType('SUID');

            if ($message->isActive() && $messageTypeCheck) {
                $message
                    ->setInvoiceNumber($invoice_requested[1])
                    ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));

                if ($message->sendMessage()) {
                    $result = $message->getResults();
                    $this->registry->register('supplier_connect_invoice_details', $result);

                    $resultPage = $this->resultPageFactory->create();
                    $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
                    if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
                        $pageMainTitle->setPageTitle(__('Invoice Number : %1', $result->getInvoiceNumber()));
                    }

                    return $resultPage;
                } else {
                    $this->messageManager->addErrorMessage(__('Failed to retrieve Invoice Details'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('Invoice Details not available'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid Invoice Number'));
        }

        if ($this->messageManager->getMessages()->getItems()) {
            $this->_redirect('*/*/index');
        }
    }

}
