<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Epicor\Comm\Model\Eav\Attribute\Data;

/**
 * Description of Erpimages
 *
 * @author ashwani.arya
 */
class Erpimages extends \Epicor\Comm\Model\Eav\Attribute\Backend\Serialized
{

    /**
     * Serialize before saving
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    
    public function beforeSave($object)
    {
        // parent::beforeSave() is not called intentionally
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($object->hasData($attrCode)) {
           $data = $object->getData($attrCode);
           if(is_array($data)){
                $object->setData($attrCode, serialize($object->getData($attrCode)));
           }else{
               $object->setData($attrCode, $object->getData($attrCode));
           }
        }

        return $this;
    } 
    
    //M1 > M2 Translation Begin (Rule 60)
    protected function _unserialize(\Magento\Framework\DataObject $object)
    {
      
        $attrCode = $this->getAttribute()->getAttributeCode();
        $objectData = $object->getData($attrCode);
        if ($objectData && !is_array($objectData)) {
            try {
                $unserialized = unserialize($objectData);
                $object->setData($attrCode, $unserialized);
            } catch (\Exception $e) {
                //$object->unsetData($attrCode);
            }
        }

        return $this;
    }
    //M1 > M2 Translation End
}