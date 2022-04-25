<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Dashboard;


class Manage extends \Epicor\AccessRight\Block\Template
{
    const FRONTEND_RESOURCE_ORDER_READ = 'Epicor_Supplier::supplier_orders_read';
    const FRONTEND_RESOURCE_INVOICE_READ = 'Epicor_Supplier::supplier_invoices_read';
    const FRONTEND_RESOURCE_RFQ_READ = 'Epicor_Supplier::supplier_rfqs_read';
    const FRONTEND_RESOURCE_PAYMENT_READ = 'Epicor_Supplier::supplier_payments_read';

    protected $supplierReminderFactory;

    protected $customerSession;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Epicor\Supplierconnect\Model\SupplierReminderFactory $supplierReminderFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        $this->supplierReminderFactory = $supplierReminderFactory;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $data
        );
    }


    public function getRfqsData() {
        $customer = $this->customerSession->getCustomer();
        $rfqsRemainderFactor = $this->supplierReminderFactory->create();
        $rfqsRemainderFactor->load($customer->getId(),'customer_id');
        return $rfqsRemainderFactor->getData();
    }

    public function checkAnyReadAllowed(){
        $allowed = false;
        $readPermissions = [
            static::FRONTEND_RESOURCE_ORDER_READ ,
            static::FRONTEND_RESOURCE_INVOICE_READ ,
            static::FRONTEND_RESOURCE_RFQ_READ ,
            static::FRONTEND_RESOURCE_PAYMENT_READ
        ];

        foreach ($readPermissions as $val){
            if($this->_isAccessAllowed($val) == true){
                $allowed = true;
                break;
            }
        }

        return $allowed;
    }
}
