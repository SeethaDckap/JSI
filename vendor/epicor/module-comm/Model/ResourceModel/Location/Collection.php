<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Location;


/**
 * Location collection model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Location\Collection
{

    protected $_eventPrefix = 'ecc_location_collection';
    protected $_eventObject = 'locations';

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\Comm\Model\Location', 'Epicor\Comm\Model\ResourceModel\Location');
    }

    public function toOptionArray()
    {
        $options = array();
        foreach ($this->getItems() as $item) {
            $options[] = array(
                'label' => $item->getName(),
                'value' => $item->getCode()
            );
        }
        return $options;
    }

}
