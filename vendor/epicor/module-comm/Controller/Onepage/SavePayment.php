<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Onepage;

class SavePayment extends \Epicor\Comm\Controller\Onepage
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->registry = $registry;
        $this->eventManager = $eventManager;
    }
    public function execute()
    {
        $payment = $this->getRequest()->getParam('payment');
        $this->registry->register('send_checkout_bsv', true);
        $this->eventManager->dispatch('save_payment_method', array('payment_method' => $payment['method']));
        parent::savePaymentAction();
    }

    }
