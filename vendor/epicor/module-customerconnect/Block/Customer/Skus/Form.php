<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Skus;


class Form extends \Magento\Framework\View\Element\Template
{//Epicor_Common_Block_Generic_Listing {

    private $_sku;
    private $_product;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Customer\SkusFactory
     */
    protected $customerconnectErpCustomerSkusFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Model\Erp\Customer\SkusFactory $customerconnectErpCustomerSkusFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->customerconnectErpCustomerSkusFactory = $customerconnectErpCustomerSkusFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    public function getCustomerSku()
    {
        if (!$this->_sku) {
            if ($this->registry->registry('sku')) {
                $this->_sku = $this->registry->registry('sku');
            } else {
                $this->_sku = $this->customerconnectErpCustomerSkusFactory->create();
            }
        }
        return $this->_sku;
    }

    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->registry->registry('product');
        }
        return $this->_product;
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }

    public function getMessagesBlock()
    {
        return $this->getLayout()->getMessagesBlock();
    }

}
