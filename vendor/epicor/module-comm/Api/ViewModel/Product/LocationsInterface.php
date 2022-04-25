<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Api\ViewModel\Product;

use Epicor\Comm\Model\Product;

interface LocationsInterface
{
    /**
     * Get product for the template.
     *
     * @param int $productId
     * @return mixed
     */
    public function getProduct($productId);

    /**
     * Sets productId.
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Get the list mode for the category page.
     *
     * @return string
     */
    public function getListMode();

    /**
     * Set list mode.
     *
     * @param string $mode
     * @return $this
     */
    public function setListMode($mode);

    /**
     * Get price to display for a given location.
     *
     * @param mixed $location
     * @param Product $product
     * @return string
     */
    public function getPriceHtml($location, Product $product);
}
