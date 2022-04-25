<?php

namespace Epicor\Dealerconnect\Model\Config\Source;
 
/**
 * Used in creating options for getting product type value
 *
 */
class Checkbox
{
    
    
    protected $_categoryFactory;
    protected $_categoryCollectionFactory;
    
    protected $_pageFactory;

    protected $_eccPacFactory;    

    public function __construct(
        \Epicor\Dealerconnect\Model\EccPacFactory $eccPacFactory
    )
    {
        $this->_eccPacFactory = $eccPacFactory;
    }

    public function getCategoryCollection()
    {
        $collection = $this->_eccPacFactory->create()->getCollection();
        //$collection->getSelect()->join(array('at' => $collection->getTable('ecc_pac_attribute')),'at.entity_id',array('attributeName' => 'at.name','parentId' => 'at.entity_id'));
        $collection->getSelect()->join(array('cs' => $collection->getTable('ecc_pac_attributes')),"cs.class_id = main_table.entity_id",array('classId' => 'cs.entity_id','cs.datatype','cs.label','attributeDesc' => 'cs.description'));
        $collection->addFieldToFilter('erp_searchable', array('eq' => 'Y'));
        //$collection->getSelect()->joinLeft(array('vl' => $collection->getTable('ecc_pac_attribute_values')),'vl.attribute_id = cs.entity_id',array('valueId' => 'vl.entity_id','attributeCode' =>'vl.code','attributeDescription'=>'vl.description'));
        $collectionResult = $collection;
        return $collectionResult;
    }

    public function toOptionArray()
    {
        $arr = $this->_toArray();
        $ret = [];
        
        foreach ($arr as $key => $value)
        {
            $keyValue = explode('_',$value, 2);         
            $ret[] = [
                'value' => $keyValue[0]."_".$key,
                'label' => $keyValue[1]
            ];
        }

        return $ret;
    }
    
    
    public function getAdminUrl()
    {
        $route = "admin/dashboard/index/";
        $params = [];
        return $this->getUrl($route, $params);
    }    

    private function _toArray()
    {
        $pacValues = $this->getCategoryCollection();
        $arrayPacVals  = array();
        foreach($pacValues->getData() as $getPacVals) {
           $arrayPacVals[$getPacVals['classId']] = $getPacVals['entity_id']. '_'. $getPacVals['attribute_class_id'].' -> '.$getPacVals['attributeDesc'];
          // $arrayPacVals[$getPacVals['classId']]['classId'] = $getPacVals['classId'];
        }        
        return $arrayPacVals;
    }    

}