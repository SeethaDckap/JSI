<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Catalog\Product\ProductList\Item\AddTo;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Add product to Lists
 *
 * @api
 * @since 100.1.1
 */
class Lists extends \Magento\Catalog\Block\Product\ProductList\Item\Block
{


    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_lists_edit';

    /**
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    private $listsFrontendRestrictedHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Epicor\Comm\Block\Catalog\Product\ListProduct $listProduct
     */
    private $listProduct;


    /**
     * Constructor
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\View\Element\Template\Context $blockcontext
     * @param \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper
     * @param \Epicor\Comm\Block\Catalog\Product\ListProduct $listProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\View\Element\Template\Context $blockcontext,
        \Epicor\Lists\Helper\Frontend\Restricted $listsFrontendRestrictedHelper,
        \Epicor\Comm\Block\Catalog\Product\ListProduct $listProduct,
        array $data = []
    )
    {
        $this->authorization = $blockcontext->getAccessAuthorization();
        $this->listsFrontendRestrictedHelper = $listsFrontendRestrictedHelper;
        $this->customerSession = $listsFrontendRestrictedHelper->getCustomerSession();
        $this->listProduct = $listProduct;
        parent::__construct($context, $data);
    }

    /**
     * Get List Class.
     *
     * @return \Epicor\Comm\Block\Catalog\Product\ListProduct
     */
    public function getListProduct()
    {
        return $this->listProduct;
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
        $locationCode = '';
        if ($this->listProduct->getSingleLocation()) {
            $locationCode = $this->listProduct->getSingleLocation()->getLocationCode();
        }
        $info[] = [
            'sku' => $product->getSku(),
            'qty' => "1",
            'location_code' => $locationCode,
            'uom' => $product->getEccDefaultUom()

        ];
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
            strpos($saveCartToList, 'L') == false ||
            $this->getProduct()->getEccConfigurator() ||
            !$this->listsFrontendRestrictedHelper->listsEnabled()
        ) {
            return '';
        }
        return parent::toHtml();

    }//end toHtml()

}
