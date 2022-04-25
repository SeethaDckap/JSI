<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Skus;

class Edit extends \Epicor\Customerconnect\Controller\Skus
{

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Customer\SkusFactory
     */
    protected $customerconnectErpCustomerSkusFactory;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Helper\Skus $customerconnectSkusHelper,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Customerconnect\Model\Erp\Customer\SkusFactory $customerconnectErpCustomerSkusFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Registry $registry
    )
    {
        $this->customerconnectErpCustomerSkusFactory = $customerconnectErpCustomerSkusFactory;
        $this->commHelper = $commHelper;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $customerconnectSkusHelper,
            $generic
        );
    }

    public function execute()
    {

        if (!$this->customerSession->authenticate($this)) {
            return;
        }

        $this->canCustomerAccessEditingSkus();

        $errorMsg = __('Error trying to retrieve SKU');

        try {
            $sku = $this->customerconnectErpCustomerSkusFactory->create()->load($this->getRequest()->get('id'));
            /* @var $sku \Epicor\Comm\Model\Quote */

            $commHelper = $this->commHelper;
            $erpAccountInfo = $commHelper->getErpAccountInfo();

            if ($sku->getCustomerGroupId() == $erpAccountInfo->getId()) {

                $this->registry->register('sku', $sku);
                $this->registry->register('product', $sku->getProduct());

                $result = $this->resultPageFactory->create();

                return $result;
            } else {
                $errorMsg .= __(': You do not have permission to access this SKU');
                throw new \Exception('Invalid customer');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($errorMsg);
            $redirectResult = $this->resultRedirectFactory->create();

            return $redirectResult->setUrl($this->_redirect->getRefererUrl());
        }
    }

}
