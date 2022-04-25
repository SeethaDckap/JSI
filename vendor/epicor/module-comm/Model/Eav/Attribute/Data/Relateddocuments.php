<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Eav\Attribute\Data;


class Relateddocuments extends \Epicor\Comm\Model\Eav\Attribute\Backend\Serialized
//Mage_Adminhtml_Block_Widget
{

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
            $datas = $object->getData($attrCode);
            if (!empty($datas)) {
                if(is_array($datas)) {
                    foreach ($datas as $key => $data) {
                        if (isset($data['is_delete']) && $data['is_delete'] == 1) {
                            unset($datas[$key]);
                        }
                    }
                    $i = 0;
                    $newdata = [];
                    foreach ($datas as $key => $data) {
                        if (isset($datas[$key])) {
                            $newdata[$i] = $data;
                            $i++;
                        }
                    }
                    $newdata = serialize($newdata);
                    $object->setData($attrCode, $newdata);
                }
            }
        }
        return $this;
    }

    //M1 > M2 Translation End
}
