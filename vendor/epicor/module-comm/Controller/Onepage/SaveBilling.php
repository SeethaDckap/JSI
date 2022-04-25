<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Onepage;

class SaveBilling extends \Epicor\Comm\Controller\Onepage
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory
    ) {
        $this->registry = $registry;
        $this->customerSessionFactory = $customerSessionFactory;
    }
    /**
     * save checkout billing address
     */
    public function execute()
    {
        $saveToErp = $this->getRequest()->getParam('billing');
        if (array_key_exists('save_in_address_book_erp', $saveToErp)) {     // if save address to erp requested, determine if to erp account data on magento or host erp account 
            $this->registry->register('newBillingAddress', $saveToErp);
            $this->customerSessionFactory->create()->setSaveBillingAddressToErp(true);   // pick up in observer
            $this->customerSessionFactory->create()->setSaveBillingAddress($saveToErp);   // pick up in observer
            if ($saveToErp['use_for_shipping']) {
                $this->customerSessionFactory->create()->setSaveShippingAddressToErp(true);
                $this->customerSessionFactory->create()->setSaveShippingAddress($saveToErp);
            }
        } else {
            $this->customerSessionFactory->create()->setSaveBillingAddressToErp(false);
        }
//                      
        $this->getOnepage()->saveCustomerOrderRef($this->getRequest()->get('po-ref'));
        parent::saveBillingAction();
    }

    }
