<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Inventory;

class GetProductDmau extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $catalogResourceModelProductFactory;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $catalogResourceModelProductFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {

        $this->request = $request;
        $this->registry = $registry;
        $this->catalogResourceModelProductFactory = $catalogResourceModelProductFactory;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context
        );
    }

    /**
     * Index action
     */
    public function execute()
    {
        $productId = $this->getRequest()->getPost("productid");
        $productCode = $this->getRequest()->getPost("productcode");
        $storeId = $this->storeManager->getStore()->getStoreId();
        $productFactory = $this->catalogResourceModelProductFactory->create();
        $description = $productFactory->getAttributeRawValue($productId, 'description', $storeId);
        $uom = $productFactory->getAttributeRawValue($productId, 'ecc_uom', $storeId);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode(array('product_code'=>$productCode , 'id'=>$productId, 'description' => $description, 'unit_of_measure_code' => $uom))); 
    }
    
}
