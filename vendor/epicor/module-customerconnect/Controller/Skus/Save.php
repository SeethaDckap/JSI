<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Skus;

class Save extends \Epicor\Customerconnect\Controller\Skus
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Customer\SkusFactory
     */
    protected $customerconnectErpCustomerSkusFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Helper\Skus $customerconnectSkusHelper,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Customerconnect\Model\Erp\Customer\SkusFactory $customerconnectErpCustomerSkusFactory,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory
    )
    {
        $this->commHelper = $commHelper;
        $this->customerconnectErpCustomerSkusFactory = $customerconnectErpCustomerSkusFactory;
        $this->catalogProductFactory = $catalogProductFactory;
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

        $error = true;
        $errorMsg = __('Error trying to save SKU');
        if (!$this->customerSession->authenticate($this)) {
            return;
        }


        try {

            $entityId = $this->getRequest()->getPost('entity_id');
            $productId = $this->getRequest()->getPost('product_id');
            $customerSku = $this->getRequest()->getPost('customer_sku');
            $description = $this->getRequest()->getPost('description');

            $customerGroupId = $this->commHelper->getErpAccountInfo()->getId();

//            $duplicatedSku = Mage::getModel('customerconnect/erp_customer_skus');
//            
//            $duplicatedSku->getCollection()
//                    ->addFieldToFilter('customer_group_id', $customerGroupId)
//                    ->addFieldToFilter('sku', $customerSku)
//                    ->getFirstItem();


            $sku = $this->customerconnectErpCustomerSkusFactory->create();

            if (!$customerSku) {
                $errorMsg .= __(': SKU is a required field');
                throw new \Exception('Invalid SKU');

//            }else if(($entityId && $duplicatedSku->getEntityId() && $duplicatedSku->getEntityId() != $entityId)
//                    || ($productId && $duplicatedSku->getEntity())){
//                $errorMsg .= $this->__(': There is another product with this SKU');
//                throw new Exception('Invalid SKU');
//                
            } else if ($entityId) {
                $sku->load($entityId);

                if ($sku->getEntityId() && $sku->getCustomerGroupId() == $customerGroupId) {
                    $error = false;
                } else {
                    $errorMsg .= __(': The SKU was not found or you do not have permission to access this SKU');
                    throw new \Exception('Invalid customer');
                }
            } else if ($productId) {
//                $sku->getCollection()
//                        ->addFieldToFilter('customer_group_id', $customerGroupId)
//                        ->addFieldToFilter('product_id', $productId)
//                        ->getFirstItem();

                $product = $this->catalogProductFactory->create()->load($productId);

                if ($productId && $product->getId()) {
                    $sku->setProductId($productId);
                    $sku->setCustomerGroupId($customerGroupId);

                    $error = false;
                } else {
                    $errorMsg .= __(': The product does not exist');
                    throw new \Exception('Invalid product');
                }
            } else {
                $errorMsg .= __(': Either Product or Entity ID is needed');
            }

            if ($error) {
                throw new \Exception('Unknown Error');
            } else {
                $sku->setSku($customerSku);
                $sku->setDescription($description);
                $sku->save();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($errorMsg);
        }

        if ($error) {
            $redirectResult = $this->resultRedirectFactory->create();

            return $redirectResult->setUrl($this->_redirect->getRefererUrl());
        } else {
            $this->messageManager->addSuccessMessage(__('SKU was successfully saved'));
            $this->_redirect('*/*');
        }
    }

}
