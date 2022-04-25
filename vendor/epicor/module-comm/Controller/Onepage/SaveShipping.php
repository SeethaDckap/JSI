<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Onepage;

class SaveShipping extends \Epicor\Comm\Controller\Onepage
{

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    public function __construct(
        \Magento\Customer\Model\SessionFactory $customerSessionFactory
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
    }
    public function execute()
    {
        $saveToErp = $this->getRequest()->getParam('shipping');
        if (array_key_exists('save_in_address_book_erp', $saveToErp)) {      // if save address to erp requested, determine if to erp account data on magento or host erp account 
            $this->customerSessionFactory->create()->setSaveShippingAddress($saveToErp);
            $this->customerSessionFactory->create()->setSaveShippingAddressToErp(true);   // pick up in observer
        } else {
            $this->customerSessionFactory->create()->setSaveShippingAddressToErp(false);
        }
        parent::saveShippingAction();
    }

    }
