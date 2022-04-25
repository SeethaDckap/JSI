<?php

namespace Epicor\Comm\Api;
/*
 * interface for add to wishlist
 */


interface AddToWishlistInterface
{


    /**
     * @param int $id
     * @param int $qty
     * @return mixed
     */
    public function addToWishlist($id, $qty);
}
