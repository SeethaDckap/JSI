<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Gor;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $commSalesOrderHelper;

    protected $checkoutSession;

    protected $commMessagingHelper;

    protected $commonHelper;

    protected $resourceConnection;

    public function __construct(
        \Epicor\Comm\Helper\Sales\Order $commSalesOrderHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->commSalesOrderHelper = $commSalesOrderHelper;
        $this->checkoutSession = $checkoutSession;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commonHelper = $commonHelper;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }


}

