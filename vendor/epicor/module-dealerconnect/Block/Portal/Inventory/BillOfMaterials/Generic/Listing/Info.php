<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Generic\Listing;

/**
 * Column Renderer for Sales ERP Account Select Grid Address
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Info extends \Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Generic\Listing\InfoDetails
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
    
    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption\CollectionFactory 
     */
    protected $eccPacAttributesOption;  
    
    
    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;    
    
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;        
    
    /**
     *
     * @var \Epicor\Dealerconnect\Model\WarrantyFactory
     */
    protected $warranty;
    
    protected $dateFormates = array('warranty_start','warranty_expiration', 'dealer_warranty_start','dealer_warranty_expiration');
    
    protected $materialTransDateFormats = [
        'warranty_start' => 'warranty_start_date',
        'warranty_expiration' => 'warranty_expiration_date',
        'dealer_warranty_start' => 'dealer_warranty_start_date',
        'dealer_warranty_expiration' => 'dealer_warranty_expiration_date'
    ];
    
    protected $warrantyInfo = array('warranty_code', 'warranty_comment', 'warranty_expiration_date', 'warranty_start_date');

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,            
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption\CollectionFactory $eccPacAttributesOption,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Dealerconnect\Model\WarrantyFactory $warranty,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->scopeConfig =$context->getScopeConfig();
        $this->commHelper = $commHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;        
        $this->eccPacAttributesOption = $eccPacAttributesOption;
        $this->warranty = $warranty;
        parent::__construct(
            $context,
            $customerconnectHelper,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $debm = $this->registry->registry('debm_info_details');
        $details = $this->getGridDetails();
        $attributes = $debm->getAttributes();
        $conversionAttributes = array();
        $conversionAttrChk = array();
        foreach($details as $convertedVals) {
            if(!$convertedVals['pac']) {
                if(in_array($convertedVals['index'], $this->dateFormates)) {
                    $_index = isset($this->materialTransDateFormats[$convertedVals['index']]) ? $this->materialTransDateFormats[$convertedVals['index']] : $convertedVals['index'];
                    $DebmVals = $this->renderDate($debm->getData($_index));
               } else {
                   switch (true) {
                       case ($convertedVals['index'] == "serial_numbers_serial_number"):
                           $DebmVals = $debm->getData($convertedVals['index'])?:$debm->getData('new_serial_number');
                           if(is_array($DebmVals)){
                               $DebmVals = implode(',', $DebmVals);
                           }
                           break;
                       case ($convertedVals['index'] == "lot_numbers_lot_number"):
                           $DebmVals = $debm->getData($convertedVals['index'])?:$debm->getData('new_lot_number');
                           if(is_array($DebmVals)){
                               $DebmVals = implode(',', $DebmVals);
                           }
                           break;
                       case ($convertedVals['index'] == "material_iD"):
                           $DebmVals = $debm->getData('material_id');
                           break;
                       case ($convertedVals['index'] == "warranty_code"):
                           $DebmVals = $debm->getData($convertedVals['index']);
                           $warranty = $this->warranty->create()->load($DebmVals, 'code');
                           if ($warranty->getId()) {
                               $DebmVals = $warranty->getDescription();
                           }
                           break;
                       default:
                           $DebmVals = $debm->getData($convertedVals['index']);
                           break;
                   }
               }
               $conversionAttributes[$convertedVals['index']] = array($convertedVals['header'],$DebmVals);
               $conversionAttrChk[$convertedVals['index']] = $DebmVals;  
            } else {
               $getAttributeValues = $this->pacAttributesRenderer($attributes,$convertedVals); 
               $conversionAttributes[$convertedVals['index']] = array($convertedVals['header'],$getAttributeValues);                 
            }
         }

        $this->_infoData = $conversionAttributes;
        $this->_infoDataCheck = $conversionAttrChk;
        $this->setTitle(__(''));
    }
    
    public function renderDate($date)
    {
        $helper = $this->customerconnectHelper;
        /* @var $helper Epicor_Customerconnect_Helper_Data */
        $data = '';
        if (!empty($date)) {
            try {
                //M1 > M2 Translation Begin (Rule 32)
                //$data = $helper->getLocalDate($row->getData($index), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
                $data = $helper->getLocalDate($date, \IntlDateFormatter::MEDIUM);
                //M1 > M2 Translation End
            } catch (\Exception $ex) {
               // $data = $row->getData($index);
            }
        }

        return $data;
    }     
    
    public  function pacAttributesRenderer($attributes,$convertedVals)
    {
        if(!empty($attributes)) {
            $explodeVals = json_decode($convertedVals['pac'],true);
            $data_attribute = $attributes->getasarrayAttribute();
            foreach ($data_attribute as $attributeVals){ 
                if ((!empty($attributeVals['code'])) && ($explodeVals['parentclass'] ==$attributeVals['class']) && ($attributeVals['code'] ==$explodeVals['pacattributeName'])){
                       return $this->switchDatatypes($explodeVals,$attributeVals['value']);
                }   
            }              
        }
    }
    
    public function getOptionCollection($code)
    {
        $collection = $this->eccPacAttributesOption->create();
        $collection->addFieldToFilter('parent_id', $code);
        $arr = array();
        if($collection->getSize() > 0) {
            foreach ($collection as $item) {
                $arr[$item->getCode()] = $item->getDescription();
            } 
        }
        return $arr;
    }  
    
    public function getComboBoxValues($code,$value) 
    {
        $options = $this->getOptionCollection($code);
        if (!empty($options)) {
           $arrayKeys = array_keys($options);
           if(in_array($value, $arrayKeys)) {
               return $options[$value];
           }
        } else {
            return ;
        }
    }
    
    public function getChecboxValues($value) 
    {
        $options = array('Y' => 'Yes', 'N' => 'No');
        if (!empty($options)) {
           $arrayKeys = array_keys($options);
           if(in_array($value, $arrayKeys)) {
               return $options[$value];
           }
        } else {
            return ;
        }
    }    

    public  function switchDatatypes($explodeVals,$value=null)
    {
        $type = $explodeVals['datatype'];
        switch ($type) {
            case "combobox": 
                return $this->getComboBoxValues($explodeVals['classId'],$value);
                break;
            case "checkbox":
                return $this->getChecboxValues($value);
                break;
            case "character":
                return $value;
                break;
            case "date":
                return $this->processDate($value);
                btreak;
            default:
                return $value;
        }        
    }

    public  function getGridDetails()
    {
        $getConfig= $this->getConfig('dealerconnect_enabled_messages/DEBM_request/grid_informationconfig');
        $oldData = unserialize($getConfig);    
        $indexVals = array();
        foreach ($oldData as $key=> $oldValues) {
           if($oldValues['hiddenpac'] !="") {
             $pac = true;
             $decodeJson = $this->decodePacJson($oldValues['hiddenpac']);
             $convertCamelCase = $decodeJson;
             if(isset($oldValues['index'])) {
                $indexVals[$oldValues['index']] =  array('pac'=>$oldValues['hiddenpac'],'index'=>$decodeJson,'header'=>$oldValues['header']);     
             }
           } else {
             $pac = false;
             if(isset($oldValues['index'])) {
                $convertCamelCase = $oldValues['index'];
                $indexVals[$oldValues['index']] = array('pac'=>$pac,'index'=>$convertCamelCase,'header'=>$oldValues['header']);    
             }
           }
        }
        return $indexVals;             
    }
    
    public function dashesToCamelCase($string, $capitalizeFirstCharacter =true) 
    {
        $str = str_replace('_', '', ucwords($string, '_'));
        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }
        return $str;
    }    
    
    
    public  function decodePacJson($jsonVals)
    {
        $deocodeJson = json_decode($jsonVals,true);
        return $deocodeJson['pacattributeName'];
    }




    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }    
    
    /**
     * 
     * Get processed date
     * @param string
     * @return string
     */
    public function processDate($rawDate)
    {
        if ($rawDate) {
            $timePart = substr($rawDate, strpos($rawDate, "T") + 1);
            if (strpos($timePart, "00:00:00") !== false) {
                $processedDate = $this->getHelper()->getLocalDate($rawDate, \IntlDateFormatter::MEDIUM, false);
            } else {
                $processedDate = $this->getHelper()->getLocalDate($rawDate, \IntlDateFormatter::MEDIUM, true);
            }
            //M1 > M2 Translation End
        } else {
            $processedDate = '';
        }
        return $processedDate;
    }
    
}
