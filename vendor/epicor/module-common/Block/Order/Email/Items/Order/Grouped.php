<?php
/**
 * Order Email items grouped renderer
 *
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Order\Email\Items\Order;

use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * @api
 * @since 100.0.2
 */
class Grouped extends  \Magento\GroupedProduct\Block\Order\Email\Items\Order\Grouped
{
    /**
     * Prepare item html
     *
     * This method uses renderer for real product type
     *
     * @return string
     */

    public function setTemplate($template) {

        // set correct template for the current template supplied
        preg_match('/items\/(.*?)\/default.phtml/s', $template, $matches);

        if(isset($matches[1])){
            return parent::setTemplate("Epicor_Common::epicor_common/email/order/items/{$matches[1]}/default.phtml");
        }
        return $template;
    }
    /**
     * Get the html for item price
     *
     * @param OrderItem $item
     * @return string
     */
    public function getItemPrice(OrderItem $item)
    {
        $block = $this->getLayout()->getBlock('item_price');
        $block->setItem($item);
        return $block->toHtml();
    }
}
