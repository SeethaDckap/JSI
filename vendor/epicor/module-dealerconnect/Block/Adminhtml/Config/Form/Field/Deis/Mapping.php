<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Deis;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{
    protected $messageTypes ="DEIS";

    protected $gridMappingHelper;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Epicor\Common\Helper\GridMapping $gridMappingHelper,
        array $data = []
    ) {
        $this->gridMappingHelper = $gridMappingHelper;
        parent::__construct(
            $context,
            $gridMappingHelper,
            $data
        );
    }


    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function setColumnName($value)
    {
        return $this->setExtraParams('rel="<%- index %>" style="width:151px"');
    }
    

    public function _toHtml()
    {
        
        if (!$this->_beforeToHtml()) {
            return '';
        }        

        if (!$this->getOptions()) {
            $this->addOption('identification_number', __('identificationNumber'));
            $this->addOption('order_number', __('orderNumber'));
            $this->addOption('serial_number', __('serialNumber'));
            $this->addOption('product_code', __('productCode'));
            $this->addOption('description', __('description'));
            $this->addOption('listing', __('listing'));
            $this->addOption('listing_start_date', __('listingDate'));
            $this->addOption('warranty_code', __('warrantyCode'));
            $this->addOption('warranty_expiration_date', __('warrantyExpirationDate'));
            $this->addOption('warranty_start_date', __('warrantyStartDate'));
            $this->addOption('location_address', __('locationAddress'));
            $this->addOption('location_address_street', __('locationAddress > street'));
            $this->addOption('location_address_name', __('locationAddress > name'));
            $this->addOption('location_address_address1', __('locationAddress > address1'));
            $this->addOption('location_address_address2', __('locationAddress > address2'));
            $this->addOption('location_address_address3', __('locationAddress > address3'));
            $this->addOption('location_address_city', __('locationAddress > city'));
            $this->addOption('location_address_country', __('locationAddress > country'));
            $this->addOption('location_address_postcode', __('locationAddress > postcode'));
            $this->addOption('location_address_telephone_number', __('locationAddress > telephoneNumber'));
            $this->addOption('location_address_fax_number', __('locationAddress > faxNumber'));
            $this->addOption('bill_of_materials', __('billOfMaterials'));
        }
        return parent::_toHtml();
    }

}
