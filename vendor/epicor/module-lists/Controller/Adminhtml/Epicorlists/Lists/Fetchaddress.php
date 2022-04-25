<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Fetchaddress extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    public function __construct(
        \Epicor\Lists\Helper\Data $listsHelper
    ) {
        $this->listsHelper = $listsHelper;
    }
    /**
     * Get customer contract addresses
     *
     * @return string
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $addressId = $data['id'];
            $customerId = $data['customer_id'];
            $result = $this->listsHelper->customerSelectedAddressById($addressId, $customerId);
        }
        echo $result;
    }

    }
