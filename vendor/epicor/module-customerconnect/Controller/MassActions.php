<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller;

abstract class MassActions extends \Epicor\Customerconnect\Controller\Generic
{
    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Preq
     */
    protected $customerconnectMessageRequestPreq;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Customerconnect\Model\PreqQueueFactory
     */
    protected $preqQueueModelFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Model\Message\Request\Preq $customerconnectMessageRequestPreq,
        \Epicor\Customerconnect\Model\PreqQueueFactory $preqQueueModelFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Data $commHelper,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->customerconnectMessageRequestPreq = $customerconnectMessageRequestPreq;
        $this->preqQueueModelFactory = $preqQueueModelFactory;
        $this->registry = $registry;
        $this->commHelper = $commHelper;
        $this->logger = $logger;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    protected function getAccountNumber()
    {
        $helper = $this->commHelper;
        $erpAccount = $helper->getErpAccountInfo();
        return $erpAccount->getAccountNumber();
    }

    protected function loadEntity($id = null)
    {
        if ($id) {
            return $this->preqQueueModelFactory->create()->load($id);
        }
        return $this->preqQueueModelFactory->create();

    }

}
