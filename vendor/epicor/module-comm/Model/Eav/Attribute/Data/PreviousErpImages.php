<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Eav\Attribute\Data;


class PreviousErpImages extends \Epicor\Comm\Model\Eav\Attribute\Backend\Serialized
{
    //M1 > M2 Translation Begin (Rule 60)
    /*
    protected function _unserialize(\Magento\Framework\DataObject $object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($object->getData($attrCode)) {
            try {
                $unserialized = unserialize($object->getData($attrCode));
                $object->setData($attrCode, $unserialized);
            } catch (\Exception $e) {
                //$object->unsetData($attrCode);
            }
        }

        return $this;
    } */
      protected function _unserialize(\Magento\Framework\DataObject $object)
    {
        //return parent::_unserialize($object);
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