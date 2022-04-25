<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


class Address extends \Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\AbstractBlock
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory
     */
    protected $commResourceCustomerErpaccountAddressCollectionFactory;

    protected $option;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory $commResourceCustomerErpaccountAddressCollectionFactory,
        \Epicor\Comm\Model\Config\Source\Yesnonulloption $yesnonulloption,
        array $data = []
    )
    {
        $this->commResourceCustomerErpaccountAddressCollectionFactory = $commResourceCustomerErpaccountAddressCollectionFactory;
        $this->option = $yesnonulloption;
        parent::__construct(
            $context,
            $registry,
            $data
        );
        $this->_title = 'Addresses';
        $this->setTemplate('epicor_comm/customer/erpaccount/edit/erpgroupaddress.phtml');
    }

    public function getOtherAddresses()
    {
        $deladdressCode = $this->getErpCustomer()->getDefaultDeliveryAddressCode();
        $delinvCode = $this->getErpCustomer()->getDefaultInvoiceAddressCode();

        $collection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create();
        $collection->addFilter('erp_customer_group_code', $this->getErpCustomer()->getErpCode());
        $collection->addFieldToFilter('erp_code', array('neq' => $deladdressCode));
        $collection->addFieldToFilter('erp_code', array('neq' => $delinvCode));
        $collection->load();

        return $collection->getItems();
    }

    public function renderAddress($erp_address_code, $erp_address = null, $type = '')
    {


        if ($erp_address_code != null || $erp_address == null) {
            $collection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create();
            $collection->addFilter('erp_customer_group_code', $this->getErpCustomer()->getErpCode());
            $collection->addFieldToFilter('erp_code', $erp_address_code);
            $erp_address = $collection->getFirstItem();
        }

        $address = '';
        $address .= (!is_null($erp_address->getErpCode())) ? 'ERP Code: ' . $erp_address->getErpCode() . '<br />' : '';
        $address .= ($erp_address->getName()) ? $erp_address->getName() . '<br />' : '';
        //M1 > M2 Translation Begin (Rule 9)
        //$address .= ($erp_address->getAddress1()) ? $erp_address->getAddress1() . '<br />' : '';
        //$address .= ($erp_address->getAddress2()) ? $erp_address->getAddress2() . '<br />' : '';
        //$address .= ($erp_address->getAddress3()) ? $erp_address->getAddress3() . '<br />' : '';
        $address .= ($erp_address->getData('address1')) ? $erp_address->getData('address1') . '<br />' : '';
        $address .= ($erp_address->getData('address2')) ? $erp_address->getData('address2') . '<br />' : '';
        $address .= ($erp_address->getData('address3')) ? $erp_address->getData('address3') . '<br />' : '';
        //M1 > M2 Translation End
        $address .= ($erp_address->getCity()) ? $erp_address->getCity() . '<br />' : '';
        $address .= ($erp_address->getCounty()) ? $erp_address->getCounty() . '<br />' : '';
        $address .= ($erp_address->getCountry()) ? $erp_address->getCountry() . '<br />' : '';
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

    //M1 > M2 Translation Begin (Rule p2-1)
    public function getOption()
    {
        return $this->option;
    }
    //M1 > M2 Translation End

}
