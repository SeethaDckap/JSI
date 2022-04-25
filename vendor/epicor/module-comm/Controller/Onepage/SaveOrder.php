<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Onepage;

class SaveOrder extends \Epicor\Comm\Controller\Onepage
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }
    /**
     * Create order action
     */
    public function execute()
    {
        $this->registry->register('checkout_save_order', true);
        parent::saveOrderAction();
    }

}
