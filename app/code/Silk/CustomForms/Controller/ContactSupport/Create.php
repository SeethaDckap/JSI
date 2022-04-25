<?php

namespace Silk\CustomForms\Controller\ContactSupport;
use Magento\Framework\App\Action\Context;

class Create extends \Magento\Framework\App\Action\Action
{
    private $logger;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->pageFactory = $pageFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        try{
            $resultPage = $this->pageFactory->create();
            return $resultPage;     
        } catch (\Exception $e) {
            $this->logger->critical('Error message', ['exception' => $e]);
        }
    }
}