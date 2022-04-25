<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Catalog\Product;

/**
 * Class Link
 * @package Epicor\Comm\Model\Catalog\Product
 */
class Link extends \Magento\Catalog\Model\Product\Link
{
    const LINK_TYPE_SUBSTITUTE = 7;

    /**
     * @return $this
     */
    public function useSubstituteLinks()
    {
        $this->setLinkTypeId(self::LINK_TYPE_SUBSTITUTE);
        return $this;
    }
}
