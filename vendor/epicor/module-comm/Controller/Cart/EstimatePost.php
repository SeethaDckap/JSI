<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Cart;

class EstimatePost extends \Epicor\Comm\Controller\Cart
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
        parent::estimatePostAction();
    }

    }
