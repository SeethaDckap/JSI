<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product\View\Type\Bundle\Option;

/**
 * Bundle option checkbox type renderer
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Checkbox extends \Epicor\Comm\Block\Catalog\Product\View\Type\Bundle\Option
{

    /**
     * Set template
     *
     * @return void
     */
    public function _construct()
    {
        $this->setTemplate('epicor_comm/catalog/product/view/type/bundle/option/checkbox.phtml');
    }

}
