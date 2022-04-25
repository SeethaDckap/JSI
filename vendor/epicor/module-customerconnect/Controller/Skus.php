<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller;


abstract class Skus extends \Epicor\Customerconnect\Controller\Generic
{

    /**
     * @var \Epicor\Customerconnect\Helper\Skus
     */
    protected $customerconnectSkusHelper;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Helper\Skus $customerconnectSkusHelper,
        \Magento\Framework\Session\Generic $generic
    )
    {
        $this->customerconnectSkusHelper = $customerconnectSkusHelper;
        $this->generic = $generic;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    protected function canCustomerAccessEditingSkus()
    {

        $canCustomerEditSkus = $this->customerconnectSkusHelper->canCustomerEditCpns();
        /* @var $canCustomerEditSkus \Epicor\Customerconnect\Helper\Skus */

        if (!$canCustomerEditSkus) {
            $this->messageManager->addErrorMessage(__('You do not have permission to access this page'));

            $redirectResult = $this->resultRedirectFactory->create();

            return $redirectResult->setUrl($this->_redirect->getRefererUrl());
        }
    }
}
