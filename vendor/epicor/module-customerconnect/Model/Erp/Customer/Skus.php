<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Erp\Customer;


class Skus extends \Epicor\Database\Model\Erp\Account\Sku
{
    protected $_product;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->catalogProductFactory = $catalogProductFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {
        $this->_init('Epicor\Customerconnect\Model\ResourceModel\Erp\Customer\Skus');
    }

    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->catalogProductFactory->create()->load($this->getProductId());
        }
        return $this->_product;
    }

    public function getProductName()
    {
        return $this->getProduct()->getName();
    }

    public function getProductSku()
    {
        return $this->getProduct()->getSku();
    }

}
