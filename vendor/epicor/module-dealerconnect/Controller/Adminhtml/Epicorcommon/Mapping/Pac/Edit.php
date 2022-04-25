<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Pac;

class Edit extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Pac
{
    /*
     * @var \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributes\CollectionFactory
     */
    protected $attributeCollection;
    /*
     * @var \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption\CollectionFactory
     */
    protected $attributeOpttionCollection;
     /*
     * @var \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesFactory
     */
    protected $pacAttributemodel;
    /*
     * @var \Epicor\Dealerconnect\Model\ResourceModel\EccPac\Collection
     */
    protected $pacCollection;
    
    public function __construct(
       \Epicor\Comm\Controller\Adminhtml\Context $context,
       \Magento\Backend\Model\Auth\Session $backendAuthSession,
       \Epicor\Dealerconnect\Model\ResourceModel\EccPac\CollectionFactory $pacCollection,     
       \Epicor\Dealerconnect\Model\EccPacAttributesFactory $pacAttributemodel,
       \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributes\CollectionFactory $attributeCollection,
       \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption\CollectionFactory $attributeOpttionCollection   
      )
    {  
        $this->pacCollection = $pacCollection;
        $this->pacAttributemodel = $pacAttributemodel;
        $this->attributeCollection = $attributeCollection;
        $this->attributeOpttionCollection = $attributeOpttionCollection;
        
        parent::__construct($context, $backendAuthSession);
    }
    
    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id', null);
        $attribute_class_id =  $this->getRequest()->getParam('class_id', null);
        $data = array();
        
        $model = $this->pacAttributemodel->create();
        if ($id) { 
            $model->load($id);
            if($model->getId()){

                 $data = $model->getData();
                 if($attribute_class_id==null){
                    $attribute_class_id = $this->getClassAttributeId($data['class_id']);
                 }
                 
                 if(isset($data['datatype']) && $data['datatype']=='ComboBox'){
                     $options_data = $this->getAttributesOptions($data['entity_id']);
                     if($options_data!=false){
                         $data['attribute_options']= $options_data;
                     }
                 }
            }else{
                $this->messageManager->addErrorMessage(__('No Attribute exist for this class'));
                $this->_redirect('*/*/');
            }    
        }
        $data['class_attribute_id'] = $attribute_class_id;
        $this->_registry->register('pac_attribute_data', $data);    
        
        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
            ->prepend( __('Pac Attribute Mapping'));
        
        return $resultPage;
    }
    
    public function getClassAttributeId($class_id){
        $collection  = $this->pacCollection->create();
        $collection->addFieldToFilter('entity_id', array('eq' => $class_id));
        $collection->load();
                
         if($collection->getSize()){
             $data = $collection->getFirstItem();
             if($data){
                return $data->getAttributeClassId();
             }
        }
        return "";
    }

      public function getAttributesOptions($parent_id) {
        //$form_attributes_options= array();
        $text_data= array();
        $text_datas = array();
         if($parent_id!=null){
            $collection  = $this->attributeOpttionCollection->create();
            $collection->addFieldToFilter('parent_id', array('eq' => $parent_id));
            $collection->load();
            $all_items = $collection->getItems();
            if($collection->getSize()>0){ 
                $i =0;
                foreach($all_items as $item){
                    if($item->getId()){ 
                        $text_datas[$i] = $item->getCode().' => '.$item->getDescription().'';
                        $i++;
                    }
                }
                if(count($text_datas) > 1) {
                    $text_data = implode(", ", $text_datas);
                } else {
                    if(!empty($text_datas)) {
                        $text_data = $text_datas[0];
                    }
                }
                return  $text_data;
                
//                foreach($all_items as $item){
//                    if($item->getId()){ 
//                        $form_attributes_options[] = array('code'=>$item->getCode(),'description'=>$item->getDescription()); 
//                    }
//                }
//                return  $form_attributes_options;
                
            }else{
                return false;
            }
         }else{
            return false;
        }
    }
}
