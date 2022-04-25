<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Skus;

class Create extends \Epicor\Customerconnect\Controller\Skus
{

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

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
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Registry $registry
    )
    {
        $this->catalogProductFactory = $catalogProductFactory;
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

        try {
            $productID = $this->getRequest()->get('id');

//            $commHelper = Mage::helper('epicor_comm');
//            /* @var $commHelper Epicor_Comm_Helper_Data */
//            $customerGroupId = $commHelper->getErpAccountInfo()->getId();
//
//            $sku = Mage::getModel('customerconnect/erp_customer_skus')
//                    ->getCollection()
//                    ->addFieldToFilter('customer_group_id', $customerGroupId)
//                    ->addFieldToFilter('product_id', $productID)
//                    ->getFirstItem();
//
//            if (is_null($sku->getEntityId())) {
            $product = $this->catalogProductFactory->create()->load($productID);
            if (is_null($product->getId())) {
                throw new \Exception('Invalid product');
            } else {
                $this->registry->register('product', $product);

                $result = $this->resultPageFactory->create();
                return $result;
            }
//            } else {
//                $this->_redirect('*/*/edit', array('id' => $sku->getEntityId()));
//            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Product not found'));

            $redirectResult = $this->resultRedirectFactory->create();

            return $redirectResult->setUrl($this->_redirect->getRefererUrl());
        }
    }

}
