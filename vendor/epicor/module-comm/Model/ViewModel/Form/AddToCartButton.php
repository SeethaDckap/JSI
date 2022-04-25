<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ViewModel\Form;

use Epicor\Comm\Api\ViewModel\Form\AddToCartButtonInterface;
use Epicor\Comm\Model\Product;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Block\Product\ListProduct;

/**
 * Class AddToCartButton.
 *
 * @package Epicor\Comm
 */
class AddToCartButton implements AddToCartButtonInterface, ArgumentInterface
{
    /**
     * @var ListProduct
     */
    private $listProduct;

    /**
     * @var array
     */
    private $postParams;

    /**
     * @var string
     */
    private $returnUrl;

    /**
     * AddToCartButton constructor.
     *
     * @param ListProduct $listProduct
     */
    public function __construct(ListProduct $listProduct)
    {
        $this->listProduct = $listProduct;
    }

    /**
     * @inheritDoc
     */
    public function getAction()
    {
        return $this->postParams['action'];
    }

    /**
     * @inheritDoc
     */
    public function getProduct()
    {
        return $this->postParams['data']['product'];
    }

    /**
     * @inheritDoc
     */
    public function getReturnUrl()
    {
        if (!empty($this->returnUrl)) {
            return $this->returnUrl;
        }
        return $this->listProduct->getReturnUrl();
    }

    /**
     * @inheritDoc
     */
    public function setReturnUrl($url)
    {
        $this->returnUrl = $url;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUenc()
    {
        return $this->postParams['data'][\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED];
    }

    /**
     * @inheritDoc
     */
    public function generatePostParams(Product $product)
    {
        $this->postParams = $this->listProduct->getAddToCartPostParams($product);
        return $this;
    }
}