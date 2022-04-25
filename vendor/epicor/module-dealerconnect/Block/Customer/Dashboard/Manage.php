<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Customer\Dashboard;


class Manage extends \Epicor\AccessRight\Block\Template
{
    const FRONTEND_RESOURCE_ORDER_READ = 'Dealer_Connect::dealer_orders_read';
    const FRONTEND_RESOURCE_QUOTE_READ = 'Dealer_Connect::dealer_quotes_read';
    const FRONTEND_RESOURCE_INVENTORY_READ = 'Dealer_Connect::dealer_inventory_read';
    const FRONTEND_RESOURCE_CLAIM_READ = 'Dealer_Connect::dealer_claim_read';

    protected $dealerReminderFactory;

    protected $customerSession;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Dealerconnect\Model\DealerReminderFactory $dealerReminderFactory,
        array $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->dealerReminderFactory = $dealerReminderFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    public function getClaimsData()
    {
        $customer = $this->customerSession->getCustomer();
        $claimsRemainderFactor = $this->dealerReminderFactory->create();
        $claimsRemainderFactor->load($customer->getId(),'customer_id');
        return $claimsRemainderFactor->getData();
    }

    public function checkAnyReadAllowed()
    {
        $allowed = false;
        $readPermissions = [
            static::FRONTEND_RESOURCE_ORDER_READ,
            static::FRONTEND_RESOURCE_QUOTE_READ,
            static::FRONTEND_RESOURCE_INVENTORY_READ,
            static::FRONTEND_RESOURCE_CLAIM_READ
        ];

        foreach ($readPermissions as $val) {
            if ($this->_isAccessAllowed($val) == true) {
                $allowed = true;
                break;
            }
        }

        return $allowed;
    }

    public function checkManageAllowed($type)
    {
        $allowed = $this->_isAccessAllowed('Dealer_Connect::dealerconnect_dashboard_manage_'.$type);
        if($type === 'reminders'){
            $allowed = $allowed && $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CLAIM_READ);
        }
        return $allowed;
    }
}
