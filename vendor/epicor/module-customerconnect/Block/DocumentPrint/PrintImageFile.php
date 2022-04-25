<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\DocumentPrint;


use Magento\Framework\View\Element\Template;

class PrintImageFile extends Template
{
    private $customerSession;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        Template\Context $context,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function getPrintDocumentUri()
    {
        $encodeFile = $this->customerSession->getEncodedPrintData();
        $src = 'data: jpg;base64,'.$encodeFile;

        return $src;
    }

    public function clearDataFile()
    {
        $this->customerSession->unsEncodedPrintData();
    }
}