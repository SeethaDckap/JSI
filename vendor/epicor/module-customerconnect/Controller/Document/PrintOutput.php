<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Document;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Sales\Controller\AbstractController\PrintAction;
use Magento\Framework\Controller\ResultFactory;

class PrintOutput extends PrintAction
{
    private $resultRedirect;
    private $printDocument;
    private $customerSession;

    public function __construct(
        CustomerSession $customerSession,
        ResultFactory $result,
        Context $context,
        OrderLoaderInterface $orderLoader,
        PageFactory $resultPageFactory
    ) {
        $this->printDocument = $this->getErpDocument();
        $this->resultRedirect = $result;
        parent::__construct($context, $orderLoader, $resultPageFactory);
        $this->customerSession = $customerSession;
    }

    /**
     * Print Order Action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $encodedFile = $this->customerSession->getEncodedPrintData();
        $type = $this->getRequest()->getParam('doc_type');

        if ($encodedFile && $this->isValidImageType($type)) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->addHandle('print');

            return $resultPage;
        }
    }

    private function isValidImageType($type): bool
    {
        $fileTypes = [
            'application/pdf' => '.pdf',
            'image/jpg' => '.jpg',
            'image/jpeg' => '.jpeg',
            'image/gif' => '.gif',
        ];

        return $type && key_exists($type, $fileTypes);
    }

    public function getErpDocument()
    {
        return ['type'=> 'jpg','url'=>'testjpg.jpg'];
    }

    public function getPrintDocumentUrl()
    {
        if ($this->isValidPrintDocumentType()) {
            return $this->printDocument['url'];
        }
    }

    private function isValidPrintDocumentType():bool
    {
        return $this->isTypePdf() || $this->isTypeJpeg() || $this->isTypeGif();
    }

    private function isTypePdf(): bool
    {
         return $this->isDocumentValidType() && $this->printDocument['type'] === 'pdf';
    }

    private function isTypeJpeg(): bool
    {
        return $this->isDocumentValidType() && $this->printDocument['type'] === 'jpeg';
    }

    private function isTypeGif(): bool
    {
        return $this->isDocumentValidType() && $this->printDocument['type'] === 'gif';
    }

    private function isDocumentValidType(): bool
    {
        return is_array($this->printDocument)
            && isset($this->printDocument['type'])
            && isset($this->printDocument['url']);
    }
}