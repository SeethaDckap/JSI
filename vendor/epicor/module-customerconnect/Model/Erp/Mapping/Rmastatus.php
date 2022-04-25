<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Erp\Mapping;


/**
 * RMA status mapping model
 * 
 * @method setCode(string $value)
 * @method setDescription(string $value)
 * @method setStoreId(int $value)
 * @method setType(string $value)
 * @method setStatusText(string $value)
 * @method setIsRmaDeleted(boolean $value)
 * @method setLastErpErrorDescription(string $value)
 * 
 * @method string getCode()
 * @method string getDescription()
 * @method int getStoreId()
 * @method string getType()
 * @method string getStatusText()
 * @method boolean getIsRmaDeleted()
 * @method string getLastErpErrorDescription()
 * 
 */
class Rmastatus extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    public function _construct()
    {
        $this->_init('Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Rmastatus');
    }

    public function toOptionArray()
    {
        $arr = array();
        $items = $this->getCollection()->getItems();
        foreach ($items as $item) {
            $arr[] = array('value' => $item->getCode(), 'label' => $item->getCode());
        }
        return $arr;
    }

    public function toGridArray()
    {
        $arr = array();
        $items = $this->getCollection()->getItems();
        foreach ($items as $item) {
            $arr[$item->getCode()] = $item->getStatus();
        }
        return $arr;
    }

}
