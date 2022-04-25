<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Api\ViewModel\Form;

use Epicor\Comm\Model\Product;

/**
 * Interface AddToCartButtonInterface
 *
 * @package Epicor\Comm
 */
interface AddToCartButtonInterface
{
    /**
     * Generate post params for the button.
     *
     * @param Product $product
     * @return $this
     */
    public function generatePostParams(Product $product);

    /**
     * Get add to cart URL.
     *
     * @return string
     */
    public function getAction();

    /**
     * Get product.
     *
     * @return string
     */
    public function getProduct();

    /**
     * Get return URL.
     *
     * @return string
     */
    public function getReturnUrl();

    /**
     * Set return URL.
     *
     * @param string $url
     * @return $this
     */
    public function setReturnUrl($url);

    /**
     * Get encrypted string.
     *
     * @return string
     */
    public function getUenc();
}
