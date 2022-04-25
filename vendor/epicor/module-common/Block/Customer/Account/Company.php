<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Common\Block\Customer\Account;


class Company extends \Magento\Framework\View\Element\Template
{


    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $erpaccountCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $erpaccountCollectionFactory,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->erpaccountCollectionFactory = $erpaccountCollectionFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
    }


    public function getCustomer()
    {
        if ($customer = $this->customerSession->getCustomer()) {
            return $customer;
        }
        return false;

    }

    public function getCompanyLists()
    {
        if ($customer = $this->customerSession->getCustomer()) {
            $ids = $customer->getAllErpAcctids();
            if (!empty($ids)) {
                $collection = $this->erpaccountCollectionFactory->create();
                /* @var $collection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Collection */
                $collection->addFieldToFilter('main_table.entity_id', ['in', $ids]);

                return $collection;
            }
        }
        return [];
    }

    public function getFavErpId()
    {
        if ($customer = $this->customerSession->getCustomer()) {
            return $customer->getFavErpId();
        }
        return false;
    }

    public function getCurrentErpId()
    {
        if ($customer = $this->customerSession->getCustomer()) {
            return $this->customerSession->getMasqueradeAccountId();
        }
        return false;
    }

    public function getStreet($erp_address)
    {

        $street = $erp_address->getData('address1');
        $street .= $erp_address->getData('address2') ? ', ' . $erp_address->getData('address2') : '';
        $street .= $erp_address->getData('address3') ? ', ' . $erp_address->getData('address3') : '';
        return $street;

    }

    public function getCountry($erp_address)
    {
        return $this->helper->getCountryName($erp_address->getCountry());
    }

    public function renderAddress($erp_address)
    {

        $address = '';
        $address .= ($erp_address->getName()) ? $erp_address->getName() . '<br />' : '';

        $street = $erp_address->getData('address1');
        $street .= $erp_address->getData('address2') ? ', ' . $erp_address->getData('address2') : '';
        $street .= $erp_address->getData('address3') ? ', ' . $erp_address->getData('address3') : '';

        $address .= ($street) ? $street . '<br />' : '';


        $address .= ($erp_address->getCity()) ? $erp_address->getCity() . '<br />' : '';
        $address .= ($erp_address->getCounty()) ? $erp_address->getCounty() . '<br />' : '';
        $address .= ($erp_address->getPostcode()) ? $erp_address->getPostcode() . '<br />' : '';
        $address .= ($erp_address->getPhone()) ? 'T : ' . $erp_address->getPhone() . '<br />' : '';
        $address .= ($erp_address->getMobileNumber()) ? 'M : ' . $erp_address->getMobileNumber() . '<br />' : '';
        $address .= ($erp_address->getFax()) ? 'F : ' . $erp_address->getFax() . '<br />' : '';
        $address .= ($erp_address->getEmail()) ? 'E : ' . $erp_address->getEmail() . '<br />' : '';
        $address .= ($erp_address->getInstructions());

        if ($address != '') {
            $html = '<address>';
            $html .= !empty($type) ? '<strong>' . $type . '</strong><br />' : '';
            $html .= $address;
            $html .= '</address>';
        } else {
            $html = '<br />No Address Set';
        }
        return $html;
    }

}