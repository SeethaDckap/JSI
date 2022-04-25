<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout\Cart\Item;

/**
 * Cart Item Configure block
 * Updates templates and blocks to show 'Update Cart' button and set right form submit url
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @module     Checkout
 */

/* comment from @ashwani.
 *  This file is not needed because layout overwrite is done by layout.xml only so we can delete this file
 */

class Configure extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Configure product view blocks
     *
     * @return \Magento\Checkout\Block\Cart\Item\Configure
     */
    protected function _prepareLayout()
    {
        // Set custom submit url route for form - to submit updated options to cart
        $block = $this->getLayout()->getBlock('product.info');
        if ($block) {
            $block->setSubmitRouteData(array(
                'route' => 'checkout/cart/updateItemOptions',
                'params' => array('id' => $this->getRequest()->getParam('id'))
            ));
        }

        // Set custom template with 'Update Cart' button
        $block = $this->getLayout()->getBlock('product.info.addtocart');
        if ($block) {
            $block->setTemplate('epicor_comm/checkout/cart/item/configure/updatecart.phtml');
        }

        return parent::_prepareLayout();
    }

}
