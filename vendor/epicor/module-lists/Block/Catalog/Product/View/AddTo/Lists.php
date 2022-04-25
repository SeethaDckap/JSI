<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Catalog\Product\View\AddTo;

/**
 * Product view add to list block
 *
 * @api
 * @since 100.1.1
 */
class Lists extends \Magento\Framework\View\Element\Template
{

    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_lists_edit';


    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    private $listsFrontendRestrictedHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Epicor\Comm\Block\Catalog\Product\View
     */
    private $productView;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Comm\Block\Catalog\Product\View $productView
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Block\Catalog\Product\View $productView,
        array $data = []
    )
    {
        $this->authorization = $context->getAccessAuthorization();
        $this->registry = $registry;
        $this->listsFrontendRestrictedHelper = $listsFrontendRestrictedHelper;
        $this->customerSession = $listsFrontendRestrictedHelper->getCustomerSession();
        $this->productView = $productView;
        parent::__construct($context, $data);
    }

    /**
     * Get product id
     *
     * @return int|null
     */
    public function getProductId()
    {
        $product = $this->registry->registry('product');
        return $product ? $product->getId() : null;
    }

    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry('product');
    }


    /**
     * get product Info.
     *
     * @return array
     */
    public function getProductInfo()
    {
        $product = $this->getProduct();
        $info['productname'] = $product->getName();
        return json_encode($info);
    }

    /**
     * Html.
     *
     * @return string
     */
    public function toHtml()
    {
        $saveCartToList = $this->listsFrontendRestrictedHelper->isCartAsListActive();
        $customer = $this->customerSession->create()->getCustomer();

        if (
            !$customer->getId() ||
            $customer->isSalesRep() ||
            $this->authorization->isAllowed(static::FRONTEND_RESOURCE) === false ||
            strpos($saveCartToList, 'D') == false ||
            $this->getProduct()->getEccConfigurator() ||
            !$this->listsFrontendRestrictedHelper->listsEnabled()
        ) {
            return '';
        }
        return parent::toHtml();

    }//end toHtml()


}
