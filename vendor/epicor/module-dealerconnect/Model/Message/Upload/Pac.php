<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Message\Upload;


/**
 * Response PAC - Part Attribute Class
 * 
 * Send customer’s delivery details up to Websales
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Pac extends \Epicor\Comm\Model\Message\Upload
{
    /*
     * Epicor\Dealerconnect\Model\EccPac $pac,
     * @var 
     */
    protected $pac;
    /*
     * Epicor\Dealerconnect\Model\EccPacAttributes $pac_attributes,
     * @var 
     */
    protected $pac_attributes;
    /*
     * Epicor\Dealerconnect\Model\EccPacAttributesOption $pac_attribiutes_options,
     * @var 
     */
    protected $pac_attribiutes_options;
    /*
     * \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributes\CollectionFactory
     */
    protected $attbutesCollection;
    /*
     * Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption\CollectionFactory
     */
    protected $attributeOptionsCollection;
    
    protected $_configWriter;    
   
    
    public function __construct(
        \Epicor\Dealerconnect\Model\EccPacFactory $pac,
        \Epicor\Dealerconnect\Model\EccPacAttributesFactory  $pac_attributes,  
        \Epicor\Dealerconnect\Model\EccPacAttributesOptionFactory  $pac_attribiutes_options,    
        \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributes\CollectionFactory  $attbutesCollection,
        \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption\CollectionFactory $attributeOptionsCollection,   
        \Epicor\Comm\Model\Context $context,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        
        $this->pac = $pac;
        $this->pac_attributes = $pac_attributes;
        $this->pac_attribiutes_options = $pac_attribiutes_options;
        $this->attbutesCollection = $attbutesCollection;
        $this->attributeOptionsCollection = $attributeOptionsCollection;
        $this->_configWriter = $configWriter;
        
        parent::__construct($context, $resource, $resourceCollection, $data);
        
        $this->setConfigBase('epicor_comm_field_mapping/pac_mapping/');
        $this->setMessageType('PAC');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_PRODUCT);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
    }
    
    /**
     * Process a request
     * 
     * @param array $requestData
     * @return 
     */
    public function processAction()
    { 
         $this->erpData = $this->getRequest()->getAttributeClass();
        /* @var $pacData \Epicor\Common\Model\Xmlvarien */
         if (!$this->erpData) {
            throw new \Exception($this->getErrorDescription(\Epicor\Comm\Model\Message::STATUS_XML_TAG_MISSING, 'AttributeClass'), \Epicor\Comm\Model\Message::STATUS_XML_TAG_MISSING);
        }
         
         $pac_class_id =  $this->erpData->getAttrClassId();
         $pac_class_description =  $this->erpData->getDescription();
         $pac_class_attributes  =  $this->erpData->getAttributes();
          
         if (is_array($pac_class_id))
            $pac_class_id = $pac_class_id[0];
         
         if(empty($pac_class_id) || $pac_class_id=="" || $pac_class_id==null){
                $error = $this->getErrorDescription(self::STATUS_GENERAL_ERROR, 'PAC Class ID Not Provided.');
                $code = self::STATUS_GENERAL_ERROR;
                throw new \Exception($error, $code);
         }
         
        $this->setMessageSubject($pac_class_id); 
        $this->setMessageSecondarySubject($pac_class_description);
        
        if (is_array($pac_class_description))
            $pac_class_description = $pac_class_description[0];
        
        $flags = $this->erpData->getData('_attributes');
        /* Model Request */
        $pac_class = $this->pac->create();
        $model_pac_class=  $pac_class->load($pac_class_id, 'attribute_class_id');
        
        $this->_exists = !$model_pac_class->isObjectNew();
        $flags = $this->erpData->getData('_attributes');
        
         if ($flags && $flags->getDelete() == 'Y') {
            if (!$model_pac_class->isObjectNew()) {
                /* Delete Whole Class Condition need to updated By Arjun */
                $this->deleteParentFromDeis($model_pac_class->getId());
                $this->deleteParentFromDeid($model_pac_class->getId());
                $this->processDelete($model_pac_class);
            }
        }else{
            /* Save Class Data */
             if(!$this->_exists){
                    $model_pac_class->setAttributeClassId($pac_class_id);
             }
             $model_pac_class->setDescription($pac_class_description);
             $model_pac_class->save(); 
             $last_class_id = $model_pac_class->getId();
            
              if (!$last_class_id) {
                   $error = $this->getErrorDescription(self::STATUS_GENERAL_ERROR, 'PAC Class Data Not Saved.');
                   $code = self::STATUS_GENERAL_ERROR;
                   throw new \Exception($error, $code);
              } 
             
              if(!empty($pac_class_attributes)){
                    $this->processAttributes($last_class_id, $pac_class_attributes);
              }else{
                  $this->filterDeleteAttributes($last_class_id);
              }
        }
    }

    /* 
     * Save PAC class attributes into the Attribute Table
     */
    public function processAttributes($class_id,$pac_class_attributes)
    {
        $data_attribute = $pac_class_attributes->getasarrayAttribute();
        
        $erpPac_Attributes_List = array();
        $erpPac_Attributes_List_keys = array();
        $count_attribute = 0;
        if(!empty($data_attribute) && count($data_attribute)>0 && $class_id!=null){ 
            foreach ($data_attribute as $_pac_attribute){ 
                if (empty($_pac_attribute) || count($_pac_attribute)==0) { 
                   $error = $this->getErrorDescription(self::STATUS_GENERAL_ERROR, 'No Attributes Data Provided');
                   $code = self::STATUS_GENERAL_ERROR;
                   throw new \Exception($error, $code);
               }
               $searchable = 'N';
                $flags = $_pac_attribute->getData('_attributes');
                if ($flags && $flags->getErpSearchable() == 'Y') 
                    $searchable = 'Y';
            
                $attributeId= ($_pac_attribute['attribute_id']) ? $_pac_attribute['attribute_id'] : null;
                $description = ($_pac_attribute['description']) ? $_pac_attribute['description'] : "";
                $lable = ($_pac_attribute['label']) ? $_pac_attribute['label'] : "";
                $datatype = ($_pac_attribute['data_type']) ? $_pac_attribute['data_type'] : "";
                
                $listOptions =  $_pac_attribute->getListOptions();
                $getListoptions = array();
                if(!empty($listOptions)) {
                    $getListoptions = $listOptions->getasarrayListOption();
                }
               
               $erpPac_Attributes_List_keys[] =$attributeId;
               
               $erpPac_Attributes_List[] = array(
                       'attribute_id'=>$attributeId,
                       'description'=>$description,
                       'lable'=>$lable,
                       'data_type'=>$datatype,
                       'searchable'=>$searchable,
                       'listoptions'=>$getListoptions
                       );
              $count_attribute++;
            }  
            
        }
        /* Delete & filter old Attibutes */
        $this->filterDeleteAttributes($class_id,$erpPac_Attributes_List_keys);
         /* Call Save Attirbutes */
        if($count_attribute>0){
            $this->saveAttributes($erpPac_Attributes_List,$class_id);
        }
    }
    
    /*
     * Save attribute Options after filtering with current & new Options. 
     */
     public function saveAttributes($list,$class_id){
         $existing_data_type = null;
         $deis_Attributes_List = array();
         foreach($list as $key=>$item){
                if($item['attribute_id']!=null && !empty($item['attribute_id'])){ 
                    $model_pac_attribute = $this->pac_attributes->create()->getCollection();
                    $model_pac_attribute->addFieldToFilter('attribute_id',$item['attribute_id']);
                    $model_pac_attribute->addFieldToFilter('class_id',$class_id);
                    $getFirstItem = $model_pac_attribute->getFirstItem();                    
                    if(!$getFirstItem->getId()){
                         $getFirstItem->setAttributeId($item['attribute_id']);
                    }else{
                        $existing_data_type = $getFirstItem->getDatatype();
                    }
                    
                    $currentDataType = $item['data_type'];
                    $getFirstItem->setClassId($class_id);
                    $getFirstItem->setDescription($item['description']);
                    $getFirstItem->setLabel($item['lable']);
                    $getFirstItem->setDatatype($item['data_type']);
                    $getFirstItem->setErpSearchable($item['searchable']);
                    $getFirstItem->save();

                    $last_attribute_entity_id = $getFirstItem->getId();
                    $getListoptions = $item['listoptions'];
                    
                    if($item['searchable'] =="N") {
                        $deis_Attributes_List[$last_attribute_entity_id] = $class_id."_".$last_attribute_entity_id;
                    }                      
                    
                    //if($last_attribute_entity_id!=null && !empty($getListoptions)){
                    if($last_attribute_entity_id!=null){    
                        /* Call Save Attribute list options */
                         $this->processAttributesOptions($last_attribute_entity_id, $getListoptions,$existing_data_type,$currentDataType,$class_id); 
                    }
                 }
         }
          //IF the attribute type is "Erpsearchable =N" then remove the configuration GRID
          $this->filterErpSearchableAttributesForDeis($class_id,$deis_Attributes_List);
    }
    
    /*
     * Processs Attribute Save. 
     */
    public function processAttributesOptions($last_attribute_entity_id, $listOptions, $existing_data_type=null,$currentDataType=null,$class_id=null)
    {  
        /* Update the existing list options */
        $attribute_option_List = array();
        $attribute_option_List_keys = array();
        $count_options =0;
         if(!empty($listOptions)){
                foreach ($listOptions as $listOption){
                    if(empty($listOption)){
                        continue;
                    }
                    $code= ($listOption['code']) ? $listOption['code'] : null;
                    $code_description = ($listOption['description']) ? $listOption['description'] : "";
                
                    if($code!=null){
                        $attribute_option_List[] = array('code'=>$code,'description'=>$code_description);
                        $attribute_option_List_keys[]=$code;
                        $count_options++;
                     }
               }
         }
        
        if($existing_data_type=='ComboBox'){
                $this->filterDeleteOldOptions($attribute_option_List_keys,$last_attribute_entity_id);
        }
        
        
        if((!empty($currentDataType)) && (!empty($existing_data_type)) && ($currentDataType !=$existing_data_type) && ($existing_data_type !="ComboBox") && (!empty($last_attribute_entity_id))) {
            $deletedIds = array($last_attribute_entity_id=>$class_id."_".$last_attribute_entity_id);
            $this->filterErpSearchableAttributesForDeis($class_id,$deletedIds);
        }  
        
        /* Attribute is new & options are new so save directly in Db */
        if($count_options>0 && $last_attribute_entity_id!=null){
            $this->saveAttributeOptions($attribute_option_List,$last_attribute_entity_id);
        }
        
    }
    
    /*
     * Check if attribute previously was combobox now change to something else.
     * So if attribute change then all old options need to deleted AND
     * if Combo box options changed then options those options which is deleted from ERP
     * should be deleted from ECC.
     */
    public function filterDeleteOldOptions($attribute_option_List_keys=array(),$parent_id,$class_id=null){
        if($parent_id!=null){
                $deletedIds = array();
                $collection  = $this->attributeOptionsCollection->create();
                if(count($attribute_option_List_keys)>0){
                    $collection->addFieldToFilter('code',  array('nin' => $attribute_option_List_keys));
                }
                $collection->addFieldToFilter('parent_id', array('eq' => $parent_id));
                $collection->load();
                $filter_items = $collection->getItems();
                if($collection->getSize()>0){
                    foreach($filter_items as $item){
                        if($item->getId()){ 
                            /* Here Arjun need to call his functions when Any attribute options get Deleted. */
                            $item->delete();
                        }
                    }
                    
                    if((!empty($parent_id) && (!empty($class_id)))) {
                       $deletedIds = array($parent_id=>$class_id."_".$parent_id);
                       $this->filterErpSearchableAttributesForDeis($class_id,$deletedIds);
                    }                      
                    
                }
        }
    }
    
    /*
     *   Save Attribute Options.
     */
    public function saveAttributeOptions($attribute_option_List,$parent_id){
          foreach ($attribute_option_List as $key=>$listOption){
                $model_attrib_options = $this->pac_attribiutes_options->create()->getCollection();
                $model_attrib_options->addFieldToFilter('code',$listOption['code']);
                $model_attrib_options->addFieldToFilter('parent_id',$parent_id);
                $getFirstItem = $model_attrib_options->getFirstItem();
                if(!$getFirstItem->getId()){
                     $getFirstItem->setCode($listOption['code']);
                }
                $getFirstItem->setParentId($parent_id);
                $getFirstItem->setDescription($listOption['description']);
                $getFirstItem->save();
             }
    }

    /*
     *  Check Any existing attribute is deleted from ERP then it must be deleted from ECC
     */
    public function filterDeleteAttributes($pac_class_id, $erpPac_Attributes_List_keys = array())
    {
        if($pac_class_id!=null){ 
            $deletedIds = array();
            $collection  = $this->attbutesCollection->create();
            if(count($erpPac_Attributes_List_keys)>0){
                $collection->addFieldToFilter('attribute_id',  array('nin' => $erpPac_Attributes_List_keys));
            }
            $collection->addFieldToFilter('class_id', array('eq' => $pac_class_id));
            $collection->load();
            $filter_items = $collection->getItems();
            
            if($collection->getSize()>0){
                foreach($filter_items as $item){
                    if($item->getId()){ 
                        $deletedIds[$item->getId()] = $pac_class_id."_".$item->getId();
                        $item->delete();
                    }
                }
                if((!empty($pac_class_id) && (!empty($deletedIds)))) {
                   $this->filterErpSearchableAttributesForDeis($pac_class_id,$deletedIds);
                }                
                
            }
             
        }
    }
    
    /*
     * Delete Whole Class along with attribute & its options.
     */
    public function processDelete($pac_class_model){
        if($pac_class_model->getId()){
            $pac_class_model->delete(); 
        }
    }
    
    
    public  function deleteParentFromDeis($parentId)
    {
        $getParentId = $parentId;
        $getGridConfig = $this->scopeConfig->getValue("dealerconnect_enabled_messages/DEIS_request/grid_config", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $fields = unserialize($getGridConfig);
        $matchedValues = array();
        $correctValues = array();
        $pacValues = array();
        foreach ($fields as $keys => $fieldsValues) {
            if($fieldsValues['pacattributes'] !="") {
               $explodeVals = explode("_",$fieldsValues['pacattributes']);
               if($getParentId == $explodeVals[0]) {
                   $matchedValues[$keys] = $fieldsValues;
                   $pacValues[$fieldsValues['pacattributes']] = $fieldsValues['pacattributes'];
               } else {
                   $correctValues[$keys] = $fieldsValues; 
               }
            } else {
                   $correctValues[$keys] = $fieldsValues; 
            }
        }
        $this->setPacConfigurations($pacValues);
        $serializedVals = serialize($correctValues);
        $setConfig = $this->setConfig($serializedVals);
        $this->_cacheManager->clean();
    }
    
    
    public  function deleteParentFromDeid($parentId)
    {
        $getParentId = $parentId;
        $getGridConfig = $this->scopeConfig->getValue("dealerconnect_enabled_messages/DEID_request/grid_informationconfig", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $fields = unserialize($getGridConfig);
        $matchedValues = array();
        $correctValues = array();
        $pacValues = array();
        foreach ($fields as $keys => $fieldsValues) {
            if($fieldsValues['hiddenpac'] !="") {
               $jsonDecode = json_decode($fieldsValues['hiddenpac'],true);
               $explodeVals = explode("_",$jsonDecode['pacattribute']);
               if($getParentId == $explodeVals[0]) {
                   $matchedValues[$keys] = $fieldsValues;
                   $pacValues[$jsonDecode['pacattribute']] = $jsonDecode['pacattribute'];
               } else {
                   $correctValues[$keys] = $fieldsValues; 
               }
            } else {
                   $correctValues[$keys] = $fieldsValues; 
            }
        }
        $this->setDeidPacConfigurations($pacValues);
        $serializedVals = serialize($correctValues);
        $setConfig = $this->setDeidConfig($serializedVals);
        $this->_cacheManager->clean();
    }    
    
    
    public function filterErpSearchableAttributesForDeis($classId,$erpAttributeList) {
        $this->setPacConfigurations($erpAttributeList);
        $this->deleteChildFromDeis($classId,$erpAttributeList);
        $this->filterErpSearchableAttributesForDeid($classId,$erpAttributeList);
    }   
    
    
    public function filterErpSearchableAttributesForDeid($classId,$erpAttributeList) {
        $this->setDeidPacConfigurations($erpAttributeList);
        $this->deleteChildFromDeid($classId,$erpAttributeList);
        $this->_cacheManager->clean();
    }       
    
    public  function deleteChildFromDeis($parentId,$childId)
    {
        $getParentId = $parentId;
        $getGridConfig = $this->scopeConfig->getValue("dealerconnect_enabled_messages/DEIS_request/grid_config", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $fields = unserialize($getGridConfig);
        $matchedValues = array();
        $correctValues = array();
        $pacValues = array();
        if(!empty($childId)) {
            foreach ($fields as $keys => $fieldsValues) {
                if($fieldsValues['pacattributes'] !="") {
                   $explodeVals = explode("_",$fieldsValues['pacattributes']);
                   if(($getParentId == $explodeVals[0]) && (in_array($fieldsValues['pacattributes'],$childId))){
                       $matchedValues[$keys] = $fieldsValues;
                   } else {
                       $correctValues[$keys] = $fieldsValues; 
                   }
                } else {
                       $correctValues[$keys] = $fieldsValues; 
                }
            }
            $serializedVals = serialize($correctValues);
            $setConfig = $this->setConfig($serializedVals);
        }
    } 
    
    
    public function setPacConfigurations($param) {
        $getGridConfig = $this->scopeConfig->getValue("dealerconnect_enabled_messages/DEIS_request/pac", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $explodeVals = explode(",",$getGridConfig);
        $pacArray = array();
        $pacArrayFilter = array();
        if((!empty($explodeVals)) && (!empty($param))) {
            foreach($explodeVals as $pacVals) {
               if(!in_array($pacVals, $param)) {
                 $pacArray[] =   $pacVals;
               } 
            }
            $this->setPacConfig($pacArray);
        }
    }     



    public  function deleteChildFromDeid($parentId,$childId)
    {
        $getParentId = $parentId;
        $getGridConfig = $this->scopeConfig->getValue("dealerconnect_enabled_messages/DEID_request/grid_informationconfig", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $fields = unserialize($getGridConfig);
        $matchedValues = array();
        $correctValues = array();
        $pacValues = array();
        if(!empty($childId)) {
            foreach ($fields as $keys => $fieldsValues) {
                if($fieldsValues['hiddenpac'] !="") {
                   $jsonDecode = json_decode($fieldsValues['hiddenpac'],true);
                   $explodeVals = explode("_",$jsonDecode['pacattribute']);
                   if(($getParentId == $explodeVals[0]) && (in_array($jsonDecode['pacattribute'],$childId))){
                       $matchedValues[$keys] = $fieldsValues;
                   } else {
                       $correctValues[$keys] = $fieldsValues; 
                   }
                } else {
                       $correctValues[$keys] = $fieldsValues; 
                }
            }
            $serializedVals = serialize($correctValues);
            $setConfig = $this->setDeidConfig($serializedVals);
        }
    }     

    
    public function setDeidPacConfigurations($param) {
        $getGridConfig = $this->scopeConfig->getValue("dealerconnect_enabled_messages/DEID_request/pacinfo", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $explodeVals = explode(",",$getGridConfig);
        $pacArray = array();
        $pacArrayFilter = array();
        if((!empty($explodeVals)) && (!empty($param))) {
            foreach($explodeVals as $pacVals) {
               if(!in_array($pacVals, $param)) {
                 $pacArray[] =   $pacVals;
               } 
            }
            $this->setDeidPacConfig($pacArray);
        }
    }       
 
    
    
    public function setConfig($value)
    {
        $this->_configWriter->save('dealerconnect_enabled_messages/DEIS_request/grid_config', $value, $this->scopeConfig::SCOPE_TYPE_DEFAULT, 0);
        return $this;
    }     
    
    
    public function setPacConfig($value)
    {
        $implode = implode(",",$value);
        $this->_configWriter->save('dealerconnect_enabled_messages/DEIS_request/pac', $implode,$this->scopeConfig::SCOPE_TYPE_DEFAULT, 0);
        return $this;
    } 
    
    
    public function setDeidConfig($value)
    {
        $this->_configWriter->save('dealerconnect_enabled_messages/DEID_request/grid_informationconfig', $value, $this->scopeConfig::SCOPE_TYPE_DEFAULT, 0);
        return $this;
    }     
    
    
    public function setDeidPacConfig($value)
    {
        $implode = implode(",",$value);
        $this->_configWriter->save('dealerconnect_enabled_messages/DEID_request/pacinfo', $implode,$this->scopeConfig::SCOPE_TYPE_DEFAULT, 0);
        return $this;
    }     
     
    /*
     *  Delete Attribute list option when Parent Attribute is deleted.
     * No need of this method right now. 
     */
//    public function processDeleteListoption($parent_id){
//        if($parent_id!=null){
//            $model_attrib_options = $this->pac_attribiutes_options->create();
//             $model_attrib_options->load($parent_id, 'parent_id');
//             if($model_attrib_options->getId())
//                    $model_attrib_options->delete();
//        } 
//    }
    
}