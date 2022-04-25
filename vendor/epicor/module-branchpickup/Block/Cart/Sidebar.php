<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
/**
 * Sidebar class to include methods from classes
 * other than Magento\Checkout\Block\Cart\Sidebar
 */
namespace Epicor\BranchPickup\Block\Cart;


use Magento\Framework\Exception\LocalizedException;

class Sidebar extends \Magento\Checkout\Block\Cart\Sidebar
{
    /* 
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $catalogResourceModelProductFactory;
    
    /*
     * @var \Magento\Checkout\CustomerData\Cart
     */
    protected $customerCart;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface $jsLayoutDataProvider
     * @param array $data
     * 
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface $jsLayoutDataProvider,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $catalogResourceModelProductFactory,
        \Magento\Checkout\CustomerData\Cart $customerCart,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = array()
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $imageHelper, $jsLayoutDataProvider, $data);
        $this->storeManager = $context->getStoreManager();
        $this->catalogResourceModelProductFactory = $catalogResourceModelProductFactory;
        $this->customerCart = $customerCart;
        $this->formKey = $formKey;
    }
    
    /**
    * Get store identifier
    *
    * @return  int
    */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get Form Key.
     *
     * @return string
     * @throws LocalizedException LocalizedException.
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
    /**
     * Get Pack Size
     * @param $productId int
     * @return string
     */
    public function getEccPackSize($productId)
    {
        return $this->catalogResourceModelProductFactory->create()->getAttributeRawValue($productId, 'ecc_pack_size', $this->getStoreId());
    }
    
}
