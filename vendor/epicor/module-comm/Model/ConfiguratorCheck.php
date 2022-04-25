<?php
/*
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model;
/*
* ConfiguratorCheck interface
 * @package Epicor\Comm
 */

use Epicor\Comm\Api\ConfiguratorCheckInterface as ConfiguratorCheckInterface;

class ConfiguratorCheck implements  ConfiguratorCheckInterface
{

     /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $catalogProductResourceModel;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;


    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product $catalogProductResourceModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->catalogProductResourceModel = $catalogProductResourceModel;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
    }


    /**
     * @param string $id
     * @param string $sku
     * @return false|mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function configuratorCheck($id, $sku)
    {
        if($id == 'AUTOCOMPLETENOTUSED' ){
            $id = $this->productFactory->create()->getIdBySku($sku);
        }

        if($id){
            //check if product is configurator
            $configurator = $id ? $this->catalogProductResourceModel->getAttributeRawValue($id,
                'ecc_configurator', $this->storeManager->getStore()->getStoreId()) : 0;

            //$configurator will only contain an array(empty) if field has never been set, otherwise it will contain 0 or 1
            $configurator = is_array($configurator) ? 0 : (int)$configurator;
            return json_encode(array('ecc_configurator' => $configurator, 'productId' => $id, 'type' => 'success'));
        }else{
            return json_encode(array('ecc_configurator' => 0, 'productId' => 'not on file', 'type' => 'failure'));
        }
    }
}

