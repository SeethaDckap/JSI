<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\Details;


class Info extends \Epicor\Dealerconnect\Block\Portal\Inventory\Details\InfoDetails
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


    protected $dateFormates = array('warranty_start_date','warranty_expiration_date','listing_date');

    protected $warrantyCollectionFactory;

    /**
     * Dealer Inventory Listing Type
     *
     * @var array
     */
    protected $_listingType = [
        'S' => 'Sale',
        'L' => 'Lease'
    ];

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption\CollectionFactory $eccPacAttributesOption,
        \Epicor\Dealerconnect\Model\ResourceModel\Warranty\CollectionFactory $warrantyCollectionFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->scopeConfig =$context->getScopeConfig();
        $this->commHelper = $commHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
        $this->eccPacAttributesOption = $eccPacAttributesOption;
        $this->warrantyCollectionFactory = $warrantyCollectionFactory;
        parent::__construct(
            $context,
            $customerconnectHelper,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $deid = $this->registry->registry('deid_order_details');
        if($deid){
            $warrantyStartDate = $deid->getWarrantyStartDate();
            $warrantyExpirationDate = $deid->getWarrantyExpirationDate();
            $infoBasicData = $this->assingValues($deid);
            $details = $this->getGridDetails();
            $attributes = $deid->getAttributes();
            $this->loadAddressData($deid);
            $conversionAttributes = array();
            foreach($details as $convertedVals) {
                if(!$convertedVals['pac']) {
                    if(in_array($convertedVals['index'], $this->dateFormates)) {
                        $DeidVals = $this->renderDate($deid->getData($convertedVals['index']));
                    } else {
                        $DeidVals = $deid->getData($convertedVals['index']);
                        if ($convertedVals['index'] == 'listing') {
                            if (isset($this->_listingType[$DeidVals])) {
                                $DeidVals = $this->_listingType[$DeidVals];
                            }
                        }
                        $index= $convertedVals['index'];
                        if (strpos($index, '>') !== false) {
                            $getUserDefined =  explode( ">", $index,4 );
                            if(isset($getUserDefined[1])) {
                                $decamelize = $this->decamelize($getUserDefined[0]);
                                $decamelizeValues = $this->decamelize($getUserDefined[1]);
                                if(count($getUserDefined) =="3") {
                                    $decamelizeValues1 = $this->decamelize($getUserDefined[2]);
                                    $DeidVals = (isset($deid->getData($decamelize)[$decamelizeValues][$decamelizeValues1]))? $deid->getData($decamelize)[$decamelizeValues][$decamelizeValues1]: '';
                                } elseif(count($getUserDefined) =="4") {
                                    $decamelizeValues1 = $this->decamelize($getUserDefined[2]);
                                    $decamelizeValues2 = $this->decamelize($getUserDefined[3]);
                                    $DeidVals = (isset($deid->getData($decamelize)[$decamelizeValues][$decamelizeValues1][$decamelizeValues2]))? $deid->getData($decamelize)[$decamelizeValues][$decamelizeValues1][$decamelizeValues2]: '';
                                } else {
                                    $DeidVals = (isset($deid->getData($decamelize)[$decamelizeValues]))? $deid->getData($decamelize)[$decamelizeValues]: '';
                                }
                                if ($this->check_your_datetime($DeidVals)) {
                                    $DeidVals = $this->renderDate($DeidVals);
                                }
                            } else {
                                $DeidVals ='';
                            }
                        }

                    }
                    $conversionAttributes[$convertedVals['index']] = array($convertedVals['header'],$DeidVals);
                } else {
                    $getAttributeValues = $this->pacAttributesRenderer($attributes,$convertedVals);
                    $conversionAttributes[$convertedVals['index']] = array($convertedVals['header'],$getAttributeValues);
                }
            }
            $this->_infoData = $conversionAttributes;
        }

        $this->setTitle(__('Inventory Information'));
    }

    public function decamelize($string) {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
    }

    public function check_your_datetime($myDateString) {
        return (bool)strtotime($myDateString);
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




    public function OrderNumberLink($fieldName,$lableVals=null)
    {
        if($fieldName =="order_number") {
            $helper = $this->customerconnectHelper;
            $erp_account_number = $helper->getErpAccountNumber();
            if($lableVals) {
                $order_requested = $this->urlEncoder->encode($this->encryptor->encrypt($erp_account_number . ']:[' . $lableVals));
                $url = $this->getUrl('*/orders/details', array('order' => $order_requested));
                $htmlVals ="<a href='".$url."'>$lableVals</a>";
            } else {
                $htmlVals ='';
            }
            return $htmlVals ;
        } else if($fieldName =="warranty_code") {
            if($lableVals) {
                $description =  $this->getWarrantyCodeDescription($lableVals);
                $getdescription = $description->getDescription();
                return ($getdescription) ? $getdescription : $lableVals;
            } else {
                return $lableVals;
            }
        } else {

            return $lableVals;
        }
    }


    public  function getWarrantyCodeDescription($code) {
        $collection = $this->warrantyCollectionFactory->create();
        $collection->addFieldToFilter('status', 'yes');
        $collection->addFieldToFilter('code', $code);
        $countItems = $collection->getFirstItem();
        return $countItems;
    }


    public  function loadAddressData($deid)
    {
        $this->_infoLocationAddress = $deid->getLocationAddress()->getData();
        $this->_infoSoldToAddress = $deid->getSoldToAddress()->getData();
        $this->_infoOwnerAddress = $deid->getOwnerAddress()->getData();
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


    public function getComboBoxValues($code,$value) {
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


    public function getChecboxValues($value) {
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
        $getConfig= $this->getConfig('dealerconnect_enabled_messages/DEID_request/grid_informationconfig');
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






    public  function assingValues($deid)
    {
        $locationFields = array('location_number', 'identification_number', 'serial_number', 'product_code', 'description','warranty_code', 'warranty_comment','warranty_expiration_date','warranty_start_date','listing','listing_date');
        $glue = '';
        $text = array();
        foreach ($locationFields as $field) {
            $fieldData = ($deid->getData($field)) ? $deid->getData($field): "";
            $text[$field]= $fieldData;
        }
        $this->_infoBasicData = $text;
    }




}