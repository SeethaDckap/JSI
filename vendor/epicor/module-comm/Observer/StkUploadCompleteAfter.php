<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer;

class StkUploadCompleteAfter  implements \Magento\Framework\Event\ObserverInterface
{

    /*
    *   store config variables
    */
    const  MSQ_AFTER_STK_CUSTOMER = 'epicor_comm_enabled_messages/msq_request/msq_after_stk_customer';
    const  MSQ_AFTER_STK = 'epicor_comm_enabled_messages/msq_request/msq_after_stk';
    const  DEFAULT_ERP_ACCOUNT = 'customer/create_account/default_erpaccount';
    
    /**
     * @var \Epicor\Comm\Model\Message\Request\MsqFactory
     */
    private $commMessageRequestMsqFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $catalogProductFactory;


    /**
     * StkUploadCompleteAfter constructor.
     * @param \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $product
     * @param \Magento\Catalog\Model\ProductFactory $catalogProductFactory
     */

    public function __construct(
        \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $product,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory
    ) {
        $this->commMessageRequestMsqFactory = $commMessageRequestMsqFactory;
        $this->scopeConfig = $scopeConfig;
        $this->catalogProductFactory = $catalogProductFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $msq = $this->commMessageRequestMsqFactory->create();

        if ($msq->isActive() && $this->scopeConfig->isSetFlag(self::MSQ_AFTER_STK, 'default', 0)) {
            $productFactory = $this->catalogProductFactory->create();
            $messageProduct = $observer->getEvent()->getMessage()->getRequest()->getProduct();
            $productId = $productFactory->getIdBySku($messageProduct->getProductCode());
            $product = $productFactory->load($productId);
            $erpAccountId = $this->scopeConfig->getValue(self::MSQ_AFTER_STK_CUSTOMER, 'default', 0);

            //if no erp account selected, use default erp
            if (!$erpAccountId) {
                $erpAccountId = $this->scopeConfig->getValue(self:: DEFAULT_ERP_ACCOUNT, 'default', 0);
            }
            // if still no erp account return wihout running msq
            if (!$erpAccountId) {
                return;
            }
            $msq->setTrigger('After STK');
            $msq->setCustomerGroupId($erpAccountId);
            $msq->setSaveProductDetails(true);
            $msq->setSessionId('After STK');
            $msq->addProduct($product, 1, false);
            $msq->sendMessage();
        }
    }
}
