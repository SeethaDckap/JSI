<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Message\Request;


/**
 * Request DMAU - Bill Of Materials Update 
 * 
 * @category   Epicor
 * @package    Epicor_DealersPortal
 * @author     Epicor Websales Team
 */
class Dmau extends \Epicor\Customerconnect\Model\Message\Request
{
    
    
    protected  $_materials = array();
    protected  $_materialsReplaced = array();
    protected  $warranty;


    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory $commResourceCustomerErpaccountAddressCollectionFactory,
        \Epicor\Dealerconnect\Model\WarrantyFactory $warranty,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->customerconnectHelper = $customerconnectHelper;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->commResourceCustomerErpaccountAddressCollectionFactory = $commResourceCustomerErpaccountAddressCollectionFactory;
        $this->commCustomerErpaccountAddressFactory = $commCustomerErpaccountAddressFactory;
        $this->warranty = $warranty;
        
        parent::__construct($context, $customerconnectMessagingHelper, $localeResolver, $resource, $resourceCollection, $data);

        $this->setMessageType('DMAU');
        $this->setLicenseType('Dealer_Portal');
        $this->setConfigBase('dealerconnect_enabled_messages/DMAU_request/');
        $this->setResultsPath('status');
    }    


    public function buildRequest()
    {

        $message['locationInventory'] = $this->_basicInformation;
        $message['locationInventory']['materials']['material'] = $this->_materials;
        $message['locationInventory']['materials']['material']['replacement'] = $this->_materialsReplaced;
        $data = $this->getMessageTemplate();
        $data['messages']['request']['body'] = array_merge($data['messages']['request']['body'], $message);
        $this->setOutXml($data);
        return true;
    }
    
    
    public function addBasicInformation($details)
    {  
        $basicDetails = $details;
        $this->_basicInformation = array(
            'locationNumber' => isset($basicDetails['location_number']) ? $basicDetails['location_number'] : '',
            'identificationNumber' => isset($basicDetails['identification_number']) ? $basicDetails['identification_number'] : '' ,
            'serialNumber' => isset($basicDetails['serial_number']) ? $basicDetails['serial_number'] : '',
        );
        return $this;
    }    
    
    
    public function addMaterials($data, $mode, $customPart)
    {  
        $data = (array)$data;
        $helper = $this->customerconnectHelper;
        if($mode === 'R'){
            $proCode = $data['productCode'];
            $desc = $data['description'];
        }else{
            $proCode = isset($data['product_code']) ? $data['product_code'] : '';
            $desc = isset($data['description']) ? $data['description'] : '';
        }
        $warrantyCode = isset($data['warranty_code']) ? $data['warranty_code'] : '' ;
        if(!empty($warrantyCode)){
            $warranty = $this->warranty->create()->load($warrantyCode, 'description');
            $warrantyCode = $warranty->getId() ? $warranty->getCode() : $warrantyCode;            
        }
        $serialNumbers = [];
        if (isset($data['serial_numbers_serial_number']) && $data['serial_numbers_serial_number'] != '') {
            $_serialNumbers = explode(',', $data['serial_numbers_serial_number']);
            foreach ($_serialNumbers as $serialNumber) {
                $serialNumbers[] = $serialNumber;
            }
        }
        $this->_materials = array(

            '_attributes' => array(
                'action' => $mode
            ),            
            
            'productCode' => $proCode,
            'serialNumbers' => (!empty($serialNumbers)) ? ['serialNumber' => $serialNumbers] : '',
            'lotNumber' => isset($data['lot_numbers_lot_number']) ? $data['lot_numbers_lot_number'] : '',
            'description' => $desc,
            'warrantyCode' => $warrantyCode,
            'warrantyComment' => isset($data['warranty_comment']) ? $data['warranty_comment'] : '',
            'warrantyStartDate' => isset($data['warranty_start']) ? $helper->getFormattedInputDate($data['warranty_start'], 'yyyy-MM-ddTHH:mm:ssZ') : '',
            'warrantyExpirationDate' => isset($data['warranty_expiration']) ? $helper->getFormattedInputDate($data['warranty_expiration'], 'yyyy-MM-ddTHH:mm:ssZ') : '',
            'dealerWarrantyCode' => '',
            'dealerWarrantyComment' => '',
            'dealerWarrantyStartDate' => '',
            'dealerWarrantyExpirationDate' => '',
        );

        return $this;
    }
   
    public function addMaterialsReplaced($data, $mode, $proCode, $description, $customPart)
    {  
        $helper = $this->customerconnectHelper;
        $warrantyCode = isset($data['warranty_code']) ? $data['warranty_code'] : '' ;
        if(!empty($warrantyCode)){
            $warranty = $this->warranty->create()->load($warrantyCode, 'description');
            
            $warrantyCode = $warranty->getId() ? $warranty->getCode() : $warrantyCode;            
        }
        $this->_materialsReplaced = array(
            
            'productCode' => $proCode,
            'description' => $description,
            'serialNumber' => isset($data['serial_numbers_serial_number']) ? $data['serial_numbers_serial_number'] : '',
            'lotNumber' => isset($data['lot_numbers_lot_number']) ? $data['lot_numbers_lot_number'] : '',
            'warrantyCode' => $customPart === 0 ? $warrantyCode : '',
            'warrantyComment' => $customPart === 0 ? (isset($data['warranty_comment']) ? $data['warranty_comment'] : '') : '',
            'warrantyStartDate' => $customPart === 0 ? (isset($data['warranty_start']) ? $helper->getFormattedInputDate($data['warranty_start'], 'yyyy-MM-ddTHH:mm:ssZ') : '') : '',
            'warrantyExpirationDate' => $customPart === 0 ? (isset($data['warranty_expiration']) ? $helper->getFormattedInputDate($data['warranty_expiration'], 'yyyy-MM-ddTHH:mm:ssZ') : '') : '',
            'dealerWarrantyCode' => $customPart === 1 ? $warrantyCode : '',
            'dealerWarrantyComment' => $customPart === 1 ? (isset($data['warranty_comment']) ? $data['warranty_comment'] : '') : '',
            'dealerWarrantyStartDate' => $customPart === 1 ? (isset($data['warranty_start']) ? $helper->getFormattedInputDate($data['warranty_start'], 'yyyy-MM-ddTHH:mm:ssZ') : '') : '',
            'dealerWarrantyExpirationDate' => $customPart === 1 ? (isset($data['warranty_expiration']) ? $helper->getFormattedInputDate($data['warranty_expiration'], 'yyyy-MM-ddTHH:mm:ssZ') : '') : '',
        );
        
        return $this;
    }  
    
    public function materialsAddition($data, $mode, $proCode, $description, $customPart)
    {  
        $helper = $this->customerconnectHelper;
        $warrantyCode = isset($data['warranty_code']) ? $data['warranty_code'] : '' ;
        if(!empty($warrantyCode)){
            $warranty = $this->warranty->create()->load($warrantyCode, 'description');
            $warrantyCode = $warranty->getId() ? $warranty->getCode() : $warrantyCode;            
        }
        $this->_materials = array(
            
            '_attributes' => array(
                'action' => $mode
            ),
            
            'productCode' => $proCode,
            'description' => $description,
            'serialNumber' => isset($data['serial_numbers_serial_number']) ? $data['serial_numbers_serial_number'] : '',
            'lotNumber' => isset($data['lot_numbers_lot_number']) ? $data['lot_numbers_lot_number'] : '',
            'warrantyCode' => $customPart === 0 ? $warrantyCode : '',
            'warrantyComment' => $customPart === 0 ? (isset($data['warranty_comment']) ? $data['warranty_comment'] : '') : '',
            'warrantyStartDate' => $customPart === 0 ? (isset($data['warranty_start']) ? $helper->getFormattedInputDate($data['warranty_start'], 'yyyy-MM-ddTHH:mm:ssZ') : '') : '',
            'warrantyExpirationDate' => $customPart === 0 ? (isset($data['warranty_expiration']) ? $helper->getFormattedInputDate($data['warranty_expiration'], 'yyyy-MM-ddTHH:mm:ssZ') : '') : '',
            'dealerWarrantyCode' => $customPart === 1 ? $warrantyCode : '',
            'dealerWarrantyComment' => $customPart === 1 ? (isset($data['warranty_comment']) ? $data['warranty_comment'] : '') : '',
            'dealerWarrantyStartDate' => $customPart === 1 ? (isset($data['warranty_start']) ? $helper->getFormattedInputDate($data['warranty_start'], 'yyyy-MM-ddTHH:mm:ssZ') : '') : '',
            'dealerWarrantyExpirationDate' => $customPart === 1 ? (isset($data['warranty_expiration']) ? $helper->getFormattedInputDate($data['warranty_expiration'], 'yyyy-MM-ddTHH:mm:ssZ') : '') : '',
        );
        
        return $this;
    } 
}