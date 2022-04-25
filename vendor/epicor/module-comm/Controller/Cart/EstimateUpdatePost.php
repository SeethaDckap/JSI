<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Cart;

class EstimateUpdatePost extends \Epicor\Comm\Controller\Cart
{

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }
    public function execute()
    {
        $this->eventManager->dispatch('checkout_cart_estimate_shipping_action', array());
        $code = (string) $this->getRequest()->getParam('estimate_method');
        if (!empty($code)) {
            $this->_getQuote()->getShippingAddress()->setShippingMethod($code)/* ->collectTotals() */->save();
        }

        $this->eventManager->dispatch('checkout_cart_estimate_shipping_update', array('quote' => $this->_getQuote()));
        $this->_goBack();
    }

    }
