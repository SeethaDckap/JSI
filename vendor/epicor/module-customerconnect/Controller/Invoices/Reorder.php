<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Invoices;

class Reorder extends \Epicor\Customerconnect\Controller\Invoices
{

    /**
     * @var \Epicor\Common\Helper\Cart
     */
    protected $commonCartHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

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
        \Epicor\Common\Helper\Cart $commonCartHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper
    )
    {
        $this->commonCartHelper = $commonCartHelper;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
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

    public function execute()
    {
        $invoice = false;
        if ($this->_loadInvoice()) {
            $invoice = $this->registry->registry('customer_connect_invoices_details');
            if (!empty($invoice)) {
                $lines = $invoice->getLines();
                if (is_object($lines)) {
                    $lineData = $lines->getasarrayLine();

                    foreach ($lineData as $x => $line) {
                        $line->setQuantity($line->getQuantities());
                        $lineData[$x] = $line;
                    }

                    $lines->setLine($lineData);
                    $invoice->setLines($lines);
                }
            }
        }

        $helper = $this->commonCartHelper;

        if (empty($invoice) || !$helper->processReorder($invoice)) {
            if (!$this->messageManager->getMessages()->getItems()) {
                $this->messageManager->addErrorMessage(__('Failed to build cart for Re-Order request'));
            }

            $this->_redirect('checkout/cart/');

            $location = $this->urlDecoder->decode($this->request->getParam('return'));

            $result = $this->resultRedirectFactory->create();
            $result->setUrl($location);

            return $result;
        } else {
            return $this->_redirect('checkout/cart/');
        }
    }

}
