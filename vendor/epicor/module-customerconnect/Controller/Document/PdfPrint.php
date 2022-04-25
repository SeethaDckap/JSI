<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Document;

use Magento\Framework\Controller\Result\Raw as RawResult;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class PdfPrint extends \Magento\Framework\App\Action\Action
{
    private $resultPageFactory;
    private $customerSession;
    private $rawResult;

    public function __construct(
        RawResult $rawResult,
        CustomerSession $customerSession,
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->rawResult = $rawResult;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $encodedPrintData = $this->customerSession->getEncodedPreqData();
        if ($encodedPrintData && $this->getRequest()->getParam('doc_type') === 'application/pdf') {
            $fileName = $this->customerSession->getPreqDocName();
            $this->rawResult->setHeader('content-type', 'application/pdf');
            $this->rawResult->setHeader('Content-Disposition', 'filename="'.$fileName.'.pdf"', true);
            $this->rawResult->setContents(base64_decode($encodedPrintData));
            return $this->rawResult;
        }
    }
}
